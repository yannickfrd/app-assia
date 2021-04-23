<?php

namespace App\Controller\Contribution;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\User;
use App\Entity\Support\Contribution;
use App\Entity\Support\SupportGroup;
use App\Event\Contribution\ContributionEvent;
use App\Form\Model\Support\ContributionSearch;
use App\Form\Model\Support\SupportContributionSearch;
use App\Form\Support\Contribution\ContributionSearchType;
use App\Form\Support\Contribution\ContributionType;
use App\Form\Support\Contribution\SupportContributionSearchType;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Support\ContributionRepository;
use App\Service\Contribution\ContributionExporter;
use App\Service\Export\ContributionFullExport;
use App\Service\Export\ContributionLightExport;
use App\Service\Indicators\ContributionIndicators;
use App\Service\Normalisation;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContributionController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $contributionRepo;

    public function __construct(EntityManagerInterface $manager, ContributionRepository $contributionRepo)
    {
        $this->manager = $manager;
        $this->contributionRepo = $contributionRepo;
    }

    /**
     * Liste des participations financières.
     *
     * @Route("/contributions", name="contributions", methods="GET|POST")
     */
    public function listContributions(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(ContributionSearchType::class, $search = new ContributionSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search);
        }
        if ($request->query->get('export2')) {
            return $this->exportLightData($search);
        }

        return $this->render('app/contribution/listContributions.html.twig', [
            'form' => $form->createView(),
            'contributions' => $pagination->paginate($this->contributionRepo->findContributionsQuery($search), $request, 20) ?? null,
        ]);
    }

    /**
     * Liste des participations financières du suivi social.
     *
     * @Route("/support/{id}/contributions", name="support_contributions", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function showSupportContributions(int $id, SupportManager $supportManager, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportContributionSearchType::class, $search = new SupportContributionSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search, $supportGroup);
        }

        $contribution = (new Contribution())
            ->setMonthContrib((new \DateTime())->modify('first day of last month'));

        $form = $this->createForm(ContributionType::class, $contribution);

        // Récupère les contributions en cache.
        // $contributions = (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(SupportGroup::CACHE_SUPPORT_CONTRIBUTIONS_KEY.$supportGroup->getId(),
        //     function (CacheItemInterface $item) use ($supportGroup, $pagination, $search, $request) {
        //         $item->expiresAfter(\DateInterval::createFromDateString('7 hours'));

        //         return $pagination->paginate($this->contributionRepo->findContributionsOfSupportQuery($supportGroup->getId(), $search), $request, 200);
        //     }
        // );

        return $this->render('app/contribution/supportContributions.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nbTotalContributions' => $request->query->count() ? $this->contributionRepo->count(['supportGroup' => $supportGroup]) : null,
            'contributions' => $pagination->paginate($this->contributionRepo->findContributionsOfSupportQuery($supportGroup->getId(), $search), $request, 200),
        ]);
    }

    /**
     * Donne les ressources.
     *
     * @Route("/support/{id}/resources", name="support_resources", methods="GET")
     *
     * @param int $id // SupportGroup
     */
    public function getResources(int $id, SupportManager $supportManager, PlaceRepository $placeRepo, EvaluationGroupRepository $evaluationRepo)
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $evaluation = $evaluationRepo->findEvaluationResourceById($id);

        $place = $placeRepo->findCurrentPlaceOfSupport($supportGroup);

        $resourcesAmt = 0;

        if ($evaluation) {
            foreach ($evaluation->getEvaluationPeople() as $evaluationPerson) {
                if ($evaluationPerson->getEvalBudgetPerson()) {
                    $resourcesAmt += $evaluationPerson->getEvalBudgetPerson()->getResourcesAmt();
                }
            }

            $contributionRate = $supportGroup->getService()->getContributionRate();
            $toPayAmt = round($resourcesAmt * $contributionRate * 100) / 100;
        }

        return $this->json([
            'action' => 'get_resources',
            'data' => [
                'resourcesAmt' => $resourcesAmt,
                'toPayAmt' => $toPayAmt ?? null,
                'contributionAmt' => $evaluation && $evaluation->getEvalBudgetGroup() ? $evaluation->getEvalBudgetGroup()->getContributionAmt() : null,
                'rentAmt' => $place ? $place->getRentAmt() : null,
            ],
        ]);
    }

    /**
     * Nouvelle participation financière.
     *
     * @Route("/support/{id}/contribution/new", name="contribution_new", methods="POST")
     */
    public function createContribution(
        SupportGroup $supportGroup,
        Request $request,
        NormalizerInterface $normalizer,
        Normalisation $normalisation,
        EventDispatcherInterface $dispatcher
    ): Response {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(ContributionType::class, $contribution = new Contribution())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $contribution->setSupportGroup($supportGroup);

            $this->manager->persist($contribution);
            $this->manager->flush();

            $dispatcher->dispatch(new ContributionEvent($contribution, $supportGroup), 'contribution.after_create');

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est enregistrée.',
                'data' => [
                    'contribution' => $normalizer->normalize($contribution, null, [
                        'groups' => ['get', 'export'],
                    ]),
                ],
            ]);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Obtenir la redevance.
     *
     * @Route("/contribution/{id}/get", name="contribution_get", methods="GET")
     */
    public function getContribution(Contribution $contribution, NormalizerInterface $normalizer): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $contribution);

        return $this->json([
            'action' => 'show',
            'data' => [
                'contribution' => $normalizer->normalize($contribution, null, ['groups' => ['get', 'view']]),
                'createdBy' => $contribution->getCreatedBy()->getFullname(),
                'updatedBy' => $contribution->getUpdatedBy()->getFullname(),
            ],
        ]);
    }

    /**
     * Modification d'une participation financière.
     *
     * @Route("/contribution/{id}/edit", name="contribution_edit", methods="POST")
     */
    public function editContribution(
        Contribution $contribution,
        Request $request,
        NormalizerInterface $normalizer,
        Normalisation $normalisation,
        EventDispatcherInterface $dispatcher
    ): Response {
        $this->denyAccessUnlessGranted('EDIT', $contribution);

        $form = $this->createForm(ContributionType::class, $contribution)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $dispatcher->dispatch(new ContributionEvent($contribution), 'contribution.after_update');

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est modifiée.',
                'data' => [
                    'contribution' => $normalizer->normalize($contribution, null, [
                        'groups' => ['get', 'export'],
                    ]),
                ],
            ]);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Supprime la participation financière.
     *
     * @Route("/contribution/{id}/delete", name="contribution_delete", methods="GET")
     */
    public function deleteContribution(Contribution $contribution, EventDispatcherInterface $dispatcher): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $contribution);

        $this->manager->remove($contribution);
        $this->manager->flush();

        $dispatcher->dispatch(new ContributionEvent($contribution), 'contribution.after_update');

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'L\'opération "'.$contribution->getTypeToString().'" est supprimée.',
        ]);
    }

    /**
     * Export un reçu de paiement au format PDF.
     *
     * @Route("/contribution/{id}/export/pdf", name="contribution_export_pdf", methods="GET")
     */
    public function exportPayment(int $id, ContributionExporter $contributionExporter): Response
    {
        $contribution = $this->contributionRepo->findContribution($id);

        $this->denyAccessUnlessGranted('VIEW', $contribution);

        $contribution->setPdfGenerateAt(new \Datetime());

        $this->manager->flush();

        return $contributionExporter->export($contribution);
    }

    /**
     * Envoie un email avec le reçu de paiement au format PDF.
     *
     * @Route("/contribution/{id}/send/pdf", name="contribution_send_pdf", methods="GET")
     */
    public function sendPaymentByEmail(int $id, ContributionExporter $contributionExporter): Response
    {
        $contribution = $this->contributionRepo->findContribution($id);

        $this->denyAccessUnlessGranted('VIEW', $contribution);

        if (!$contributionExporter->sendEmail($contribution)) {
            return $this->json([
                'action' => 'error',
                'alert' => 'danger',
                'msg' => 'Le suivi n\'a pas d\'adresse e-mail renseignée.',
            ]);
        }

        $this->manager->flush();

        return $this->json([
            'action' => 'send_receipt',
            'alert' => 'success',
            'msg' => 'Le reçu du paiement a été envoyé par email.',
        ]);
    }

    /**
     * Affiche les indicateurs mensuels des participations financières.
     *
     * @Route("/contribution/indicators", name="contribution_indicators", methods="GET|POST")
     */
    public function showContributionIndicators(Request $request, ContributionIndicators $indicators): Response
    {
        $search = $this->getContributionSearch();

        $form = $this->createForm(ContributionSearchType::class, $search)
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportFullData($search);
        }

        $datas = $indicators->getIndicators(
            $this->contributionRepo->findContributionsForIndicators($search),
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
        $contributions = $this->contributionRepo->findContributionsToExport($search, $supportGroup);

        if (!$contributions) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('contributions');
        }

        return (new ContributionFullExport($router))->exportData($contributions);
    }

    /**
     * Exporte les données.
     */
    protected function exportLightData(ContributionSearch $search, UrlGeneratorInterface $router = null)
    {
        $contributions = $this->contributionRepo->findContributionsToExport($search);

        if (!$contributions) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('contributions');
        }

        return (new ContributionLightExport($router))->exportData($contributions);
    }

    protected function getContributionSearch()
    {
        $today = new \DateTime('today');
        $search = (new ContributionSearch())
            ->setType([1])
            ->setStart(new \DateTime($today->format('Y').'-01-01'))
            ->setEnd($today);

        if (User::STATUS_SOCIAL_WORKER === $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        return $search;
    }
}
