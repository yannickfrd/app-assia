<?php

namespace App\Service\Payment;

use App\Entity\People\RolePerson;
use App\Entity\Support\Payment;
use App\Notification\PaymentNotification;
use App\Service\ExportPDF;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class PaymentExporter
{
    private $paymentNotification;
    private $exportPDF;
    private $renderer;
    private $appEnv;

    public function __construct(
        PaymentNotification $paymentNotification,
        ExportPDF $exportPDF,
        Environment $renderer,
        string $appEnv
    ) {
        $this->paymentNotification = $paymentNotification;
        $this->exportPDF = $exportPDF;
        $this->renderer = $renderer;
        $this->appEnv = $appEnv;
    }

    public function sendEmail(Payment $payment): bool
    {
        $supportGroup = $payment->getSupportGroup();

        $emails = [];
        $fullnames = [];
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (RolePerson::ROLE_CHILD != $supportPerson->getRole()) {
                $person = $supportPerson->getPerson();
                $email = $person->getEmail();
                if ($email) {
                    $emails[] = $email;
                }
                $fullnames[] = $person->getFullname();
            }
        }

        if (0 === count($emails)) {
            return false;
        }

        $title = $this->getTitle($payment);
        $path = $this->create($payment);
        $date = $payment->getPaymentDate() ? $payment->getPaymentDate()->format('d-m-Y') :
            $payment->getCreatedAt()->format('d-m-Y');

        $this->paymentNotification->sendPayment(
            $emails,
            'ESPERER 95 | '.$title.' '.$date.' | '.join(' - ', $fullnames),
            [
                'payment' => $payment,
                'support' => $supportGroup,
            ],
            $path
        );

        $payment->setMailSentAt(new \Datetime());

        return true;
    }

    public function export(Payment $payment): StreamedResponse
    {
        $this->create($payment);

        return $this->exportPDF->download($this->appEnv);
    }

    private function create(Payment $payment): string
    {
        $supportGroup = $payment->getSupportGroup();
        $title = $this->getTitle($payment);
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();

        $content = $this->renderer->render('app/payment/pdf/paymentPdf.html.twig', [
            'title' => $title,
            'logo_path' => $this->exportPDF->getPathImage($logoPath),
            'payment' => $payment,
            'support' => $supportGroup,
        ]);

        $this->exportPDF->createDocument($content, $title, $logoPath, $supportGroup->getHeader()->getFullname());

        return $this->exportPDF->save();
    }

    private function getTitle(Payment $payment)
    {
        switch ($payment->getType()) {
            case Payment::CONTRIBUTION:
                if ($payment->getPaymentDate()) {
                    return 'Reçu de paiement';
                }

                return 'Avis d\'échéance';
                break;

            case Payment::LOAN:
                return 'Avance financière';
                break;

            case Payment::DEPOSIT:
                return 'Reçu de caution';
                break;

            case Payment::REPAYMENT:
                return 'Reçu de paiement';
                break;

            case Payment::DEPOSIT_REFUNT:
                return 'Reçu de restitution de caution';
                break;

            default:
                return 'Reçu';
                break;
        }
    }
}
