<?php

namespace App\Export;

use App\Service\Export;
use App\Entity\Accommodation;
use App\Entity\SupportPerson;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SupportPersonExport
{
    protected $arrayData;

    public function __construct()
    {
        $this->arrayData = [];
    }

    /**
     * Exporte les données.
     */
    public function exportData($supports)
    {
        $arrayData = [];
        $i = 0;

        foreach ($supports as $supportPerson) {
            if (0 == $i) {
                $arrayData[] = array_keys($this->getDatas($supportPerson));
            }
            $arrayData[] = $this->getDatas($supportPerson);
            ++$i;
        }

        return (new Export('export_suivis', 'xlsx', $arrayData, null))->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    public function getDatas(SupportPerson $supportPerson): array
    {
        $person = $supportPerson->getPerson();
        $supportGroup = $supportPerson->getSupportGroup();
        $groupPeople = $supportGroup->getGroupPeople();

        $startAccommodations = [];
        $endAccommodations = [];
        $endReasonAccommodations = [];
        $nameAccommodations = [];
        $addressAccommodations = [];
        $cityAccommodations = [];
        $zipcodeAccommodations = [];

        $accommodationPeople = $person->getAccommodationPeople();
        foreach ($accommodationPeople as $accommodationPerson) {
            $startAccommodations[] = $this->formatDate($accommodationPerson->getStartDate());
            $endAccommodations[] = $this->formatDate($accommodationPerson->getEndDate());
            $endReasonAccommodations[] = $accommodationPerson->getEndReasonToString();
            /** @var Accommodation */
            $accommodation = $accommodationPerson->getAccommodationGroup()->getAccommodation();
            $nameAccommodations[] = $accommodation->getName().' ';
            $addressAccommodations[] = $accommodation->getAddress();
            $cityAccommodations[] = $accommodation->getCity();
            $zipcodeAccommodations[] = $accommodation->getZipcode();
        }

        return [
            'N° Groupe' => $groupPeople->getId(),
            'N° Suivi groupe' => $supportGroup->getId(),
            'N° Personne' => $person->getId(),
            'N° Suivi personne' => $supportPerson->getId(),
            'Nom' => $person->getLastname(),
            'Prénom' => $person->getFirstname(),
            'Date de naissance' => $this->formatDate($person->getBirthdate()),
            'Typologie familiale' => $groupPeople->getFamilyTypologyToString(),
            'Nb de personnes' => $groupPeople->getNbPeople(),
            'Rôle dans le groupe' => $supportPerson->getRoleToString(),
            'DP' => $supportPerson->getHead() ? 'Oui' : 'Non',
            'Statut suivi (personne)' => $supportPerson->getStatusToString(),
            'Date début suivi (personne)' => $this->formatDate($supportPerson->getStartDate()),
            'Date fin suivi (personne)' => $this->formatDate($supportPerson->getEndDate()),
            'Situation à la fin (personne)' => $supportPerson->getEndStatusToString(),
            'Commentaire situation à la fin (personne)' => $supportPerson->getEndStatusComment(),
            'Référent social' => $supportGroup->getReferent()->getFullname(),
            'Référent social suppléant' => $supportGroup->getReferent2() ? $supportGroup->getReferent2()->getFullname() : null,
            'Pôle' => $supportGroup->getService()->getPole()->getName(),
            'Service' => $supportGroup->getService()->getName(),
            'Dispositif' => $supportGroup->getDevice() ? $supportGroup->getDevice()->getName() : '',
            'Date début hébergement' => join(', ', $startAccommodations),
            'Date fin hébergement' => join(', ', $endAccommodations),
            'Motif fin hébergement' => join(', ', $endReasonAccommodations),
            'Nom du logement/ hébergement' => join(', ', $nameAccommodations),
            'Adresse' => join(', ', $addressAccommodations),
            'Ville' => join(', ', $cityAccommodations),
            'Département' => join(', ', $zipcodeAccommodations),
            'Statut suivi (groupe)' => $supportGroup->getStatusToString(),
            'Date début suivi (groupe)' => $this->formatDate($supportGroup->getStartDate()),
            'Date fin suivi (groupe)' => $this->formatDate($supportGroup->getEndDate()),
            'Situation à la fin (groupe)' => $supportGroup->getEndStatusToString(),
            'Commentaire situation à la fin (groupe)' => $supportGroup->getEndStatusComment(),
        ];
    }

    public function formatDate($date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d')) : null;
    }
}
