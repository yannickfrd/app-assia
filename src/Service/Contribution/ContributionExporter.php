<?php

namespace App\Service\Contribution;

use App\Entity\People\RolePerson;
use App\Entity\Support\Contribution;
use App\Notification\ContributionNotification;
use App\Service\ExportPDF;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class ContributionExporter
{
    public const TITLE = 'Reçu de paiement';

    private $contributionNotification;
    private $exportPDF;
    private $renderer;
    private $appEnv;

    public function __construct(
        ContributionNotification $contributionNotification,
        ExportPDF $exportPDF,
        Environment $renderer,
        string $appEnv
    ) {
        $this->contributionNotification = $contributionNotification;
        $this->exportPDF = $exportPDF;
        $this->renderer = $renderer;
        $this->appEnv = $appEnv;
    }

    public function sendEmail(Contribution $contribution): bool
    {
        $supportGroup = $contribution->getSupportGroup();

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

        $path = $this->create($contribution);
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

        $contribution->setMailSentAt(new \Datetime());

        return true;
    }

    public function export(Contribution $contribution): StreamedResponse
    {
        $this->create($contribution);

        return $this->exportPDF->download($this->appEnv);
    }

    private function create(Contribution $contribution): string
    {
        $supportGroup = $contribution->getSupportGroup();
        $title = $contribution->getPaymentDate() ? self::TITLE : 'Avis d\'échéance';
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();

        $content = $this->renderer->render('app/contribution/contributionExport.html.twig', [
            'title' => $title,
            'logo_path' => $this->exportPDF->getPathImage($logoPath),
            'contribution' => $contribution,
            'support' => $supportGroup,
        ]);

        $this->exportPDF->createDocument($content, $title, $logoPath, $supportGroup->getHeader()->getFullname());

        return $this->exportPDF->save();
    }
}
