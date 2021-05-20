<?php

namespace App\Controller\Payment;

use App\Entity\Support\Payment;
use App\Repository\Support\PaymentRepository;
use App\Service\Payment\PaymentExporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportPaymentController extends AbstractController
{
    private $manager;
    private $paymentRepo;

    public function __construct(EntityManagerInterface $manager, PaymentRepository $paymentRepo)
    {
        $this->manager = $manager;
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

        $payment->setPdfGenerateAt(new \Datetime());

        $this->manager->flush();

        return $paymentExporter->export($payment);
    }

    /**
     * Envoie un email avec le reçu de paiement au format PDF.
     *
     * @Route("/payment/{id}/send/pdf", name="payment_send_pdf", methods="GET")
     */
    public function sendPaymentByEmail(int $id, PaymentExporter $paymentExporter): Response
    {
        $payment = $this->paymentRepo->findPayment($id);

        $this->denyAccessUnlessGranted('VIEW', $payment);

        if (!$paymentExporter->sendEmail($payment)) {
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
}
