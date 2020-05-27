<?php

namespace App\Controller;

use App\Service\Pagination;
use App\Entity\Contribution;
use App\Entity\SupportGroup;
use App\Service\Normalisation;
use App\Controller\Traits\CacheTrait;
use App\Form\Model\ContributionSearch;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ContributionRepository;
use App\Repository\SupportGroupRepository;
use App\Form\Contribution\ContributionType;
use App\Controller\Traits\ErrorMessageTrait;
use App\Form\Model\SupportContributionSearch;
use App\Repository\EvaluationGroupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Collections\ArrayCollection;
use App\Form\Contribution\ContributionSearchType;
use App\Form\Contribution\SupportContributionSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ContributionController extends AbstractController
{
    use ErrorMessageTrait;
    use CacheTrait;

    private $manager;
    private $repo;
    private $repoSupportGroup;

    public function __construct(EntityManagerInterface $manager, ContributionRepository $repo, SupportGroupRepository $repoSupportGroup)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->repoSupportGroup = $repoSupportGroup;
    }

    /**
     * Liste des participations financières.
     *
     * @Route("contributions", name="contributions", methods="GET|POST")
     */
    public function listContributions(ContributionSearch $search = null, Request $request, Pagination $pagination): Response
    {
        $search = new ContributionSearch();
        if ($this->getUser()->getStatus() == 1) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = ($this->createForm(ContributionSearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/contribution/listContributions.html.twig', [
            'form' => $form->createView(),
            'contributions' => $pagination->paginate($this->repo->findAllContributionsQuery($search), $request, 20) ?? null,
        ]);
    }

    /**
     * Liste des participations financières du suivi social.
     *
     * @Route("support/{id}/contributions", name="support_contributions", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function listSupportContributions(int $id, SupportContributionSearch $search = null, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $search = new SupportContributionSearch();

        $formSearch = $this->createForm(SupportContributionSearchType::class, $search);
        $formSearch->handleRequest($request);

        $contribution = (new Contribution())
            ->setContribDate((new \DateTime())->modify('-1 month')->modify('first day of this month'));

        $form = $this->createForm(ContributionType::class, $contribution);

        return $this->render('app/contribution/supportContributions.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'contributions' => $pagination->paginate($this->repo->findAllContributionsFromSupportQuery($supportGroup->getId(), $search), $request, 10) ?? null,
        ]);
    }

    /**
     * Donne les ressources.
     *
     * @Route("support/{id}/resources", name="support_resources", methods="GET")
     *
     * @param int $id // SupportGroup
     */
    public function getResources(int $id, EvaluationGroupRepository $repoEvaluation)
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);
        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $evaluation = $repoEvaluation->findEvaluationResourceById($id);

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
            $contribAmt = round($resourcesAmt * $contributionRate);
        }

        return $this->json([
            'code' => 200,
            'action' => 'getResources',
            'data' => [
                'salaryAmt' => $salaryAmt,
                'resourcesAmt' => $resourcesAmt,
                'contribAmt' => $contribAmt ?? null,
            ],
        ], 200);
    }

    /**
     * Nouvelle participation financière.
     *
     * @Route("support/{id}/contribution/new", name="contribution_new", methods="POST")
     *
     * @param int $id // SupportGroup
     */
    public function newContribution(int $id, Contribution $contribution = null, Request $request, NormalizerInterface $normalizer, Normalisation $normalisation): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);

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

        return $this->json([
            'code' => 200,
            'action' => 'show',
            'data' => [
                'contribution' => $normalizer->normalize($contribution, null, ['groups' => 'get']),
            ],
        ], 200);
    }

    /**
     * Modification d'une participation financière.
     *
     * @Route("contribution/{id}/edit", name="contribution_edit", methods="POST")
     */
    public function editContribution(Contribution $contribution, Request $request, NormalizerInterface $normalizer): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $contribution->getSupportGroup());

        $form = ($this->createForm(ContributionType::class, $contribution))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateContribution($contribution, $normalizer);
        }

        return $this->getErrorMessage($form);
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

        return $this->json([
            'code' => 200,
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'La redevance est supprimée.',
        ], 200);
    }

    /**
     * Crée la contribution une fois le formulaire soumis et validé.
     */
    protected function createContribution(SupportGroup $supportGroup, Contribution $contribution, NormalizerInterface $normalizer): Response
    {
        $supportGroup->setUpdatedAt(new \DateTime());

        $this->manager->persist($contribution);
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'La redevance est enregistrée.',
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
        $contribution->getSupportGroup()->setUpdatedAt(new \DateTime());

        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'update',
            'alert' => 'success',
            'msg' => 'La redevance est modifiée.',
            'data' => [
                'contribution' => $normalizer->normalize($contribution, null, [
                    'groups' => ['get', 'export'],
                ]),
            ],
        ], 200);
    }
}