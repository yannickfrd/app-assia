<?php

namespace App\Service\Export;

use App\Entity\Support\Contribution;
use App\Service\ExportExcel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContributionFullExport extends ExportExcel
{
    protected $router;

    public function __construct(?UrlGeneratorInterface $router)
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

        $this->createSheet('export_paiements', 'xlsx', $arrayData, 16);
        $this->addTotalRow();

        return $this->exportFile();
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
            'N° suivi' => $contribution->getSupportGroup()->getId(),
            'Nom' => $person ? $person->getLastname() : null,
            'Prénom' => $person ? $person->getFirstname() : null,
            'Date de naissance' => $person ? $this->formatDate($person->getBirthdate()) : null,
            'Date d\'arrivée' => $supportGroup->getStartDate() ? $this->formatDate($supportGroup->getStartDate()) : null,
            'Service' => $supportGroup->getService()->getName(),
            'Pôle' => $supportGroup->getService()->getPole()->getName(),
            'Type d\'opération' => $contribution->getTypeToString(),
            'PF - Date début de la période' => $this->formatDate($contribution->getStartDate()),
            'PF - Date fin de la période' => $this->formatDate($contribution->getEndDate()),
            'PF - Montant ressources' => $contribution->getResourcesAmt(),
            'Montant loyer' => $contribution->getRentAmt(),
            'Montant APL' => $contribution->getAplAmt(),
            'Montant à régler' => $contribution->getToPayAmt(),
            'Montant réglé' => $contribution->getPaidAmt(),
            'Montant restant dû' => $contribution->getStillToPayAmt(),
            'Date de l\'opération' => $this->formatDate($contribution->getPaymentDate()),
            'Mode de règlement' => $contribution->getPaymentType() ? $contribution->getPaymentTypeToString() : null,
            'Caution - Montant restitué' => $contribution->getReturnAmt(),
            'Commentaire' => $contribution->getComment(),
            'Date de création' => $this->formatDate($contribution->getCreatedAt()),
            'Créé par' => $contribution->getCreatedBy()->getFullname(),
            'Date de modification' => $this->formatDate($contribution->getUpdatedAt()),
            'Modifié par' => $contribution->getUpdatedBy()->getFullname(),
        ];
    }
}
