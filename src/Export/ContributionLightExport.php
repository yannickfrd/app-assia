<?php

namespace App\Export;

use App\Entity\Contribution;
use App\Service\ExportExcel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContributionLightExport
{
    protected $router;

    public function __construct(UrlGeneratorInterface $router = null)
    {
        $this->router = $router;
    }

    /**
     * Exporte les données.
     */
    public function exportData($contributions)
    {
        $arrayData[] = array_keys($this->getDatas($contributions[0]));

        foreach ($contributions as $contribution) {
            $arrayData[] = $this->getDatas($contribution);
        }

        $export = new ExportExcel('export_paiements', 'xlsx', $arrayData, 16);
        $export->addTotalRow();

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(Contribution $contribution): array
    {
        $supportGroup = $contribution->getSupportGroup();
        $person = null;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getHead()) {
                $person = $supportPerson->getPerson();
            }
        }

        return [
            'N° contribution' => $contribution->getId(),
            'Nom' => $person ? $person->getFullname() : null,
            'Service' => $contribution->getSupportGroup()->getService()->getName(),
            'Type' => $contribution->getTypeToString(),
            'Mois (Date)' => $this->formatDate($contribution->getMonth()),
            'Montant dû (€)' => $contribution->getDueAmt(),
            'Montant réglé (€)' => $contribution->getPaidAmt(),
            'Restant dû (€)' => $contribution->getStillDueAmt(),
            'Date de réglement' => $this->formatDate($contribution->getPaymentDate()),
            'Mode de réglement' => $contribution->getPaymentType() ? $contribution->getPaymentTypeToString() : null,
            'Commentaire' => $contribution->getComment(),
            'TS' => $contribution->getCreatedBy()->getFullname(),
        ];
    }

    public function formatDate($date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d')) : null;
    }
}
