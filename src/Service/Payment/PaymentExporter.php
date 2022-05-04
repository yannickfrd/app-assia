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
    private $downloadsDirectory;

    public function __construct(
        PaymentNotification $paymentNotification,
        ExportPDF $exportPDF,
        Environment $renderer,
        string $downloadsDirectory
    ) {
        $this->paymentNotification = $paymentNotification;
        $this->exportPDF = $exportPDF;
        $this->renderer = $renderer;
        $this->downloadsDirectory = $downloadsDirectory;
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

        $organizationName = $supportGroup->getService()->getPole()->getOrganization()->getName();

        $this->paymentNotification->sendPayment(
            $emails,
             $organizationName.' | '.$title.' '.$date.' | '.join(' - ', $fullnames),
            [
                'payment' => $payment,
                'support' => $supportGroup,
            ],
            $path
        );

        $payment->setMailSentAt(new \DateTime());

        return true;
    }

    public function export(Payment $payment): StreamedResponse
    {
        $this->create($payment);

        return $this->exportPDF->download();
    }

    private function create(Payment $payment): string
    {
        $supportGroup = $payment->getSupportGroup();
        $title = $this->getTitle($payment);
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();

        $content = $this->renderer->render('app/payment/pdf/payment_pdf.html.twig', [
            'title' => $title,
            'logo_path' => $this->exportPDF->getPathImage($logoPath),
            'payment' => $payment,
            'support' => $supportGroup,
        ]);

        $this->exportPDF->createDocument($content, $title, $logoPath, $supportGroup->getHeader()->getFullname());

        return $this->exportPDF->save($this->downloadsDirectory);
    }

    private function getTitle(Payment $payment): string
    {
        switch ($payment->getType()) {
            case Payment::CONTRIBUTION:
                return $payment->getPaymentDate() ? 'Reçu de paiement' : 'Avis d\'échéance';
            case Payment::LOAN:
                return 'Avance financière';
            case Payment::DEPOSIT:
                return 'Reçu de caution';
            case Payment::REPAYMENT:
                return 'Reçu de paiement';
            case Payment::DEPOSIT_REFUNT:
                return 'Reçu de restitution de caution';
            default:
                return 'Reçu';
        }
    }
}
