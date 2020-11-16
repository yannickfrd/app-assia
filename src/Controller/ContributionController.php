<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Contribution;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Form\Contribution\ContributionSearchType;
use App\Form\Contribution\ContributionType;
use App\Form\Contribution\SupportContributionSearchType;
use App\Form\Model\ContributionSearch;
use App\Form\Model\SupportContributionSearch;
use App\Repository\AccommodationRepository;
use App\Repository\ContributionRepository;
use App\Repository\EvaluationGroupRepository;
use App\Service\Export\ContributionFullExport;
use App\Service\Export\ContributionLightExport;
use App\Service\Indicators\ContributionIndicators;
use App\Service\Normalisation;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContributionController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repoContribution;

    public function __construct(EntityManagerInterface $manager, ContributionRepository $repoContribution)
    {
        $this->manager = $manager;
        $this->repoContribution = $repoContribution;
    }

    /**
     * Liste des participations financières.
     *
     * @Route("contributions", name="contributions", methods="GET|POST")
     */
    public function listContributions(Request $request, Pagination $pagination): Response
    {
        $search = new ContributionSearch();
        if (User::STATUS_SOCIAL_WORKER == $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = ($this->createForm(ContributionSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search);
        }
        if ($request->query->get('export2')) {
            return $this->exportLightData($search);
        }

        return $this->render('app/contribution/listContributions.html.twig', [
            'form' => $form->createView(),
            'contributions' => $pagination->paginate($this->repoContribution->findAllContributionsQuery($search), $request, 20) ?? null,
        ]);
    }

    /**
     * Liste des participations financières du suivi social.
     *
     * @Route("support/{id}/contributions", name="support_contributions", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function showSupportContributions(int $id, SupportManager $supportManager, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $search = new SupportContributionSearch();

        $formSearch = ($this->createForm(SupportContributionSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search, $supportGroup);
        }

        $contribution = (new Contribution())
            ->setMonthContrib((new \DateTime())->modify('-1 month')->modify('first day of this month'));

        $form = $this->createForm(ContributionType::class, $contribution);

        // Récupère les contributions en cache.
        $contributions = (new FilesystemAdapter())->get(SupportGroup::CACHE_SUPPORT_CONTRIBUTIONS_KEY.$supportGroup->getId(),
            function (CacheItemInterface $item) use ($supportGroup, $pagination, $search, $request) {
                $item->expiresAfter(\DateInterval::createFromDateString('7 hours'));

                return $pagination->paginate($this->repoContribution->findAllContributionsFromSupportQuery($supportGroup->getId(), $search), $request, 200);
            }
        );

        return $this->render('app/contribution/supportContributions.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nbTotalContributions' => $request->query->count() ? $this->repoContribution->count(['supportGroup' => $supportGroup]) : null,
            'contributions' => $contributions,
        ]);
    }

    /**
     * Donne les ressources.
     *
     * @Route("support/{id}/resources", name="support_resources", methods="GET")
     *
     * @param int $id // SupportGroup
     */
    public function getResources(int $id, SupportManager $supportManager, AccommodationRepository $repoAccommodation, EvaluationGroupRepository $repoEvaluation)
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $evaluation = $repoEvaluation->findEvaluationResourceById($id);

        $accommodation = $repoAccommodation->findCurrentAccommodationOfSupport($supportGroup);

        $salaryAmt = 0;
        $resourcesAmt = 0;

        if ($evaluation) {
            foreach ($evaluation->getEvaluationPeople() as $evaluationPerson) {
                if ($evaluationPerson->getEvalBudgetPerson()) {
                    $salaryAmt += $evaluationPerson->getEvalBudgetPerson()->getSalaryAmt();
                    $resourcesAmt += $evaluationPerson->getEvalBudgetPerson()->getResourcesAmt();
                }
            }

            $contributionRate = $supportGroup->getService()->getContributionRate();
            $toPayAmt = round($resourcesAmt * $contributionRate * 100) / 100;
        }

        return $this->json([
            'code' => 200,
            'action' => 'getResources',
            'data' => [
                'salaryAmt' => $salaryAmt,
                'resourcesAmt' => $resourcesAmt,
                'toPayAmt' => $toPayAmt ?? null,
                'contributionAmt' => $evaluation && $evaluation->getEvalBudgetGroup() ? $evaluation->getEvalBudgetGroup()->getContributionAmt() : null,
                'rentAmt' => $accommodation ? $accommodation->getRentAmt() : null,
            ],
        ], 200);
    }

    /**
     * Nouvelle participation financière.
     *
     * @Route("support/{id}/contribution/new", name="contribution_new", methods="POST")
     */
    public function newContribution(SupportGroup $supportGroup, Contribution $contribution = null, Request $request, NormalizerInterface $normalizer, Normalisation $normalisation): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $contribution = (new Contribution())
            ->setSupportGroup($supportGroup);

        $form = ($this->createForm(ContributionType::class, $contribution))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createContribution($supportGroup, $contribution, $normalizer);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Obtenir la redevance.
     *
     * @Route("contribution/{id}/get", name="contribution_get", methods="GET")
     */
    public function getContribution(Contribution $contribution, NormalizerInterface $normalizer): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $contribution->getSupportGroup());

        // $form = $this->createForm(ContributionType::class, $contribution);

        return $this->json([
            'code' => 200,
            'action' => 'show',
            'data' => [
                // 'contribution' => $this->render('app/contribution/modalContribution.html.twig', [
                //     'form' => $form->createView(),
                // ]),
                'contribution' => $normalizer->normalize($contribution, null, ['groups' => ['get', 'view']]),
                'createdBy' => $contribution->getCreatedBy()->getFullname(),
                'updatedBy' => $contribution->getUpdatedBy()->getFullname(),
            ],
        ], 200);
    }

    /**
     * Modification d'une participation financière.
     *
     * @Route("contribution/{id}/edit", name="contribution_edit", methods="POST")
     */
    public function editContribution(Contribution $contribution, Request $request, NormalizerInterface $normalizer, Normalisation $normalisation): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $contribution->getSupportGroup());

        $form = ($this->createForm(ContributionType::class, $contribution))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateContribution($contribution, $normalizer);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Supprime la participation financière.
     *
     * @Route("contribution/{id}/delete", name="contribution_delete", methods="GET")
     */
    public function deleteContribution(Contribution $contribution): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $contribution->getSupportGroup());

        $this->manager->remove($contribution);
        $this->manager->flush();

        $this->discache($contribution->getSupportGroup());

        return $this->json([
            'code' => 200,
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est supprimée.',
        ], 200);
    }

    /**
     * Affiche les indicateurs mensuels des participations financières.
     *
     * @Route("contribution/indicators", name="contribution_indicators", methods="GET|POST")
     */
    public function showContributionIndicators(Request $request, ContributionIndicators $indicators): Response
    {
        $search = $this->getContributionSearch();

        $form = ($this->createForm(ContributionSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search);
        }

        $datas = $indicators->getIndicators(
            $this->repoContribution->findAllContributionsForIndicators($search),
            $search,
        );

        return $this->render('app/contribution/contributionIndicators.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
        ]);
    }

    /**
     * Exporte les données.
     *
     * @param ContributionSearch|SupportContributionSearch $search
     */
    protected function exportFullData($search, $supportGroup = null, UrlGeneratorInterface $router = null)
    {
        $supports = $this->repoContribution->findContributionsToExport($search, $supportGroup);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new ContributionFullExport($router))->exportData($supports);
    }

    /**
     * Exporte les données.
     */
    protected function exportLightData(ContributionSearch $search, UrlGeneratorInterface $router = null)
    {
        $supports = $this->repoContribution->findContributionsToExport($search);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new ContributionLightExport($router))->exportData($supports);
    }

    /**
     * Crée la contribution une fois le formulaire soumis et validé.
     */
    protected function createContribution(SupportGroup $supportGroup, Contribution $contribution, NormalizerInterface $normalizer): Response
    {
        $supportGroup->setUpdatedAt(new \DateTime());

        $contribution->setStillToPayAmt($contribution->getToPayAmt() - $contribution->getPaidAmt());

        $this->manager->persist($contribution);
        $this->manager->flush();

        $this->discache($supportGroup);

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est enregistrée.',
            'data' => [
                'contribution' => $normalizer->normalize($contribution, null, [
                    'groups' => ['get', 'export'],
                ]),
            ],
        ], 200);
    }

    /**
     * Met à jour la contribution une fois le formulaire soumis et validé.
     */
    protected function updateContribution(Contribution $contribution, NormalizerInterface $normalizer): Response
    {
        $contribution->setStillToPayAmt();
        $contribution->getSupportGroup()->setUpdatedAt(new \DateTime());

        $this->manager->flush();

        $this->discache($contribution->getSupportGroup(), true);

        return $this->json([
            'code' => 200,
            'action' => 'update',
            'alert' => 'success',
            'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est modifiée.',
            'data' => [
                'contribution' => $normalizer->normalize($contribution, null, [
                    'groups' => ['get', 'export'],
                ]),
            ],
        ], 200);
    }

    protected function getContributionSearch()
    {
        $today = new \DateTime('today');
        $search = (new ContributionSearch())
            ->setType([1])
            ->setStart(new \DateTime($today->format('Y').'-01-01'))
            ->setEnd($today);

        if (User::STATUS_SOCIAL_WORKER == $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        return $search;
    }

    /**
     * Supprime les contributions en cache du suivi.
     */
    protected function discache(SupportGroup $supportGroup, $isUpdate = false): bool
    {
        $cache = new FilesystemAdapter();

        if (false === $isUpdate) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_CONTRIBUTIONS_KEY.$supportGroup->getId());
        }

        return $cache->deleteItem(SupportGroup::CACHE_SUPPORT_CONTRIBUTIONS_KEY.$supportGroup->getId());
    }
}
