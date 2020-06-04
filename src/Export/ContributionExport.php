<?php

namespace App\Export;

use App\Entity\Contribution;
use App\Service\ExportExcel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ContributionExport
{
    protected $router;

    public function __construct(UrlGeneratorInterface $router)
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

        $export = new ExportExcel('export_participations', 'xlsx', $arrayData, 16);
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
            'N° suivi' => $contribution->getSupportGroup()->getId(),
            'Nom' => $person ? $person->getLastname() : null,
            'Prénom' => $person ? $person->getFirstname() : null,
            'Date de naissance' => $person ? $this->formatDate($person->getBirthdate()) : null,
            'Service' => $contribution->getSupportGroup()->getService()->getName(),
            'Pôle' => $contribution->getSupportGroup()->getService()->getPole()->getName(),
            'Type' => $contribution->getTypeToString(),
            'Mois concerné (Date)' => $this->formatDate($contribution->getContribDate()),
            'PF - Montant salaire (€)' => $contribution->getSalaryAmt(),
            'PF - Montant ressources (€)' => $contribution->getResourcesAmt(),
            'Montant dû (€)' => $contribution->getContribAmt(),
            'Montant réglé (€)' => $contribution->getPaymentAmt(),
            'Restant dû (€)' => $contribution->getStillDueAmt(),
            'Date de réglement' => $this->formatDate($contribution->getPaymentDate()),
            'Mode de réglement' => $contribution->getPaymentType() ? $contribution->getPaymentTypeToString() : null,
            'Caution - Date de restitution' => $this->formatDate($contribution->getReturnDate()),
            'Caution - Montant restitué (€)' => $contribution->getReturnAmt(),
            'Commentaire' => $contribution->getComment(),
            'Date de création' => $this->formatDate($contribution->getCreatedAt()),
            'Créé par' => $contribution->getCreatedBy()->getFullname(),
            'Date de modification' => $this->formatDate($contribution->getUpdatedAt()),
            'Modifié par' => $contribution->getUpdatedBy()->getFullname(),
            'Url' => $this->router->generate('support_contributions', [
                'id' => $supportGroup->getId(),
                'contributionId' => $contribution->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL),
        ];
    }

    public function formatDate($date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d')) : null;
    }
}
