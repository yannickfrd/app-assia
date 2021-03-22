<?php

namespace App\EventDispatcher\Support;

use App\Entity\People\RolePerson;
use App\Entity\Support\Contribution;
use App\Entity\Support\SupportGroup;
use App\Event\Support\ContributionEvent;
use App\Notification\ContributionNotification;
use App\Service\ExportPDF;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Twig\Environment;

class ExportPaymentSubScriber implements EventSubscriberInterface
{
    public const TITLE = 'Reçu de paiement';

    private $contributionNotification;
    private $exportPDF;
    private $renderer;

    public function __construct(
        ContributionNotification $contributionNotification,
        ExportPDF $exportPDF,
        Environment $renderer
    ) {
        $this->contributionNotification = $contributionNotification;
        $this->exportPDF = $exportPDF;
        $this->renderer = $renderer;
    }

    public static function getSubscribedEvents()
    {
        return [
            'contribution.export' => 'exportPdf',
            'contribution.send_email' => 'sendEmail',
        ];
    }

    public function sendEmail(ContributionEvent $event): void
    {
        $contribution = $event->getContribution();
        $supportGroup = $event->getSupportGroup();

        $emails = [];
        $fullnames = [];
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (RolePerson::ROLE_CHILD != $supportPerson->getRole()) {
                $emails[] = $supportPerson->getPerson()->getEmail();
                $fullnames[] = $supportPerson->getPerson()->getFullname();
            }
        }

        $path = $this->createDocument($contribution, $supportGroup);
        $date = $contribution->getPaymentDate() ? $contribution->getPaymentDate()->format('d-m-Y') :
            $contribution->getCreatedAt()->format('d-m-Y');

        $this->contributionNotification->sendContribution(
            $emails,
            'ESPERER 95 | '.self::TITLE.' '.$date.' | '.join(' - ', $fullnames),
            [
                'contribution' => $contribution,
                'support' => $supportGroup,
            ],
            $path
        );
    }

    public function exportPdf(ContributionEvent $event)
    {
        $contribution = $event->getContribution();
        $supportGroup = $event->getSupportGroup();

        $this->createDocument($contribution, $supportGroup);
        // return $this->exportPDF->download();
    }

    private function createDocument(Contribution $contribution, SupportGroup $supportGroup)
    {
        $title = $contribution->getPaymentDate() ? self::TITLE : 'Avis d\'échéance';
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();

        $content = $this->renderer->render('app/support/contribution/contributionExport.html.twig', [
            'title' => $title,
            'logo_path' => $this->exportPDF->getPathImage($logoPath),
            'contribution' => $contribution,
            'support' => $supportGroup,
        ]);

        $this->exportPDF->createDocument($content, $title, $logoPath, $supportGroup->getHeader()->getFullname());

        return $this->exportPDF->save();
    }
}
