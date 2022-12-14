<?php

declare(strict_types=1);

namespace App\Controller\Payment;

use App\Entity\Support\Payment;
use App\Repository\Support\PaymentRepository;
use App\Service\Payment\PaymentExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ExportPaymentController extends AbstractController
{
    private $em;
    private $paymentRepo;

    public function __construct(EntityManagerInterface $em, PaymentRepository $paymentRepo)
    {
        $this->em = $em;
        $this->paymentRepo = $paymentRepo;
    }

    /**
     * Export un reçu de paiement au format PDF.
     *
     * @Route("/payment/{id}/export/pdf", name="payment_export_pdf", methods="GET")
     */
    public function exportPayment(int $id, PaymentExporter $paymentExporter): Response
    {
        $payment = $this->paymentRepo->findPayment($id);

        $this->denyAccessUnlessGranted('VIEW', $payment);

        $payment->setPdfGenerateAt(new \DateTime());

        $this->em->flush();

        return $paymentExporter->export($payment);
    }

    /**
     * Envoie un email avec le reçu de paiement au format PDF.
     *
     * @Route("/payment/{id}/send/pdf", name="payment_send_pdf", methods="GET")
     */
    public function sendPaymentByEmail(int $id, PaymentExporter $paymentExporter, TranslatorInterface $translator): JsonResponse
    {
        $payment = $this->paymentRepo->findPayment($id);

        $this->denyAccessUnlessGranted('VIEW', $payment);

        if (!$paymentExporter->sendEmail($payment)) {
            return $this->json([
                'action' => 'error',
                'alert' => 'danger',
                'msg' => $translator->trans('payment.no_email', [], 'app'),
            ]);
        }

        $this->em->flush();

        return $this->json([
            'action' => 'send_receipt',
            'alert' => 'success',
            'msg' => $translator->trans('payment.sent_by_email_successfully', [], 'app'),
            'payment' => ['id' => $id],
        ]);
    }
}
