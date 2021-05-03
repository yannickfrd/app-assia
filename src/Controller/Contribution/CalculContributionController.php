<?php

namespace App\Controller\Contribution;

use App\Entity\Support\Contribution;
use App\Form\Support\Contribution\ContributionType;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\PlaceRepository;
use App\Service\Contribution\ContributionCalculator;
use App\Service\SupportGroup\SupportManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalculContributionController extends AbstractController
{
    public function __construct()
    {
    }

    /**
     * Donne les ressources.
     *
     * @Route("/support/{id}/resources", name="support_resources", methods="GET")
     *
     * @param int $id SupportGroup
     */
    public function getResources(
        int $id,
        SupportManager $supportManager,
        PlaceRepository $placeRepo,
        EvaluationGroupRepository $evaluationRepo
    ) {
        if (null === $supportGroup = $supportManager->getSupportGroup($id)) {
            throw $this->createAccessDeniedException('Ce suivi n\'existe pas.');
        }

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $evaluation = $evaluationRepo->findEvaluationBudget($supportGroup);

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
     * Liste des participations financières.
     *
     * @Route("/support/{id}/contribution/calcul", name="support_contribution_calcul", methods="GET|POST")
     */
    public function calculContribution(
        int $id,
        Request $request,
        EvaluationGroupRepository $evaluationRepo,
        SupportManager $supportManager,
        ContributionCalculator $contributionCalculator
    ): Response {
        if (null === $supportGroup = $supportManager->getSupportGroup($id)) {
            throw $this->createAccessDeniedException('Ce suivi n\'existe pas.');
        }
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $evaluationGroup = $evaluationRepo->findEvaluationBudget($supportGroup);

        // $contribution = (new Contribution())->setSupportGroup($supportGroup);

        $this->createForm(ContributionType::class, $contribution = new Contribution())
            ->handleRequest($request);

        $contribution = $contributionCalculator->calculate($supportGroup, $evaluationGroup, $contribution);

        // return $this->render('app/contribution/_contributionCalcul.html.twig', [
        //     'contribution' => $contribution,
        // ]);

        return $this->json([
            'action' => 'get_contribution',
            'data' => [
                'contribution' => $contribution,
                'view' => $this->renderView('app/contribution/_contributionCalcul.html.twig', [
                    'contribution' => $contribution,
                ]),
            ],
        ]);
    }
}
