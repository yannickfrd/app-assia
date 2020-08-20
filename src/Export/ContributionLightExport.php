<?php

namespace App\Export;

use App\Entity\Contribution;
use App\Service\ExportExcel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContributionLightExport
{
    use ExportExcelTrait;

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
            'N° opération' => $contribution->getId(),
            'Nom' => $person ? $person->getFullname() : null,
            'Service' => $supportGroup->getService()->getName(),
            'Type d\'opération' => $contribution->getTypeToString(),
            'Mois concerné (Date)' => $this->formatDate($contribution->getMonthContrib()),
            'Montant à régler (€)' => $contribution->getToPayAmt(),
            'Montant réglé (€)' => $contribution->getPaidAmt(),
            'Restant dû (€)' => $contribution->getStillToPayAmt(),
            'Date de l\'opération' => $this->formatDate($contribution->getPaymentDate()),
            'Mode de règlement' => $contribution->getPaymentType() ? $contribution->getPaymentTypeToString() : null,
            'Commentaire' => $contribution->getComment(),
            'Travailleur social' => $contribution->getCreatedBy()->getFullname(),
        ];
    }
}
