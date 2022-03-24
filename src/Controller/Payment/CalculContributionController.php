<?php

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Entity\Support\Payment;
use App\Form\Support\Payment\PaymentType;
use App\Service\Payment\ContributionCalculator;
use App\Service\SupportGroup\SupportManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class CalculContributionController extends AbstractController
{
    private $supportManager;
    private $paymentCalculator;

    public function __construct(SupportManager $supportManager, ContributionCalculator $paymentCalculator)
    {
        $this->supportManager = $supportManager;
        $this->paymentCalculator = $paymentCalculator;
    }

    /**
     * Liste des participations financières.
     *
     * @Route("/support/{id}/contribution/calcul", name="support_payment_calcul", methods="GET|POST")
     */
    public function calculContribution(int $id, Request $request): JsonResponse
    {
        $supportGroup = $this->supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $this->createForm(PaymentType::class, $initPayment = new Payment())
            ->handleRequest($request);

        $newPayment = $this->paymentCalculator->calculate($supportGroup, $initPayment);

        $view = $this->renderView('app/payment/_contributionCalcul.html.twig', [
            'support' => $supportGroup,
            'payment' => $newPayment,
        ]);

        return $this->json([
            'action' => 'get_contribution',
            'data' => [
                'payment' => $newPayment,
                'view' => $view,
            ],
        ]);
    }
}
