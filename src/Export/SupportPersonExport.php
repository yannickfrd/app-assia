<?php

namespace App\Export;

use App\Entity\SupportPerson;
use App\Service\Export;
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

        $export = new Export('export_suivis', 'xlsx', $arrayData, null);

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     *
     * @return array
     */
    public function getDatas(SupportPerson $supportPerson)
    {
        $person = $supportPerson->getPerson();
        $supportGroup = $supportPerson->getSupportGroup();
        $groupPeople = $supportGroup->getGroupPeople();

        $nameAccommodations = [];
        $addressAccommodations = [];
        $cityAccommodations = [];
        $zipCodeAccommodations = [];

        $accommodationPersons = $person->getAccommodationPersons();
        foreach ($accommodationPersons as $accommodationPerson) {
            $accommodations = $accommodationPerson->getAccommodationGroup()->getAccommodation();
            $nameAccommodations[] = $accommodations->getName();
            $addressAccommodations[] = $accommodations->getAddress();
            $cityAccommodations[] = $accommodations->getCity();
            $zipCodeAccommodations[] = $accommodations->geZipCode();
        }

        return [
            'N° Groupe' => $groupPeople->getId(),
            'N° Suivi groupe' => $supportGroup->getId(),
            // "N° Personne" => $person->getId(),
            // "N° Suivi personne" => $supportPerson->getId(),
            'Nom' => $person->getLastname(),
            'Prénom' => $person->getFirstname(),
            'Date de naissance' => $this->formatDate($person->getBirthdate()),
            'Typologie familiale' => $groupPeople->getFamilyTypologyToString(),
            'Nb de personnes' => $groupPeople->getNbPeople(),
            'Rôle dans le groupe' => $supportPerson->getRoleToString(),
            'DP' => $supportPerson->getHead() ? 'Oui' : 'Non',
            'Statut' => $supportPerson->getStatusToString(),
            'Date début suivi' => $this->formatDate($supportPerson->getStartDate()),
            'Date fin suivi' => $this->formatDate($supportPerson->getEndDate()),
            'Situation à la fin' => $supportPerson->getEndStatus() ? $supportPerson->getEndStatusToString() : null,
            'Commentaire situation à la fin' => $supportPerson->getEndStatusComment(),
            'Référent social' => $supportGroup->getReferent()->getFullname(),
            'Référent social suppléant' => $supportGroup->getReferent2() ? $supportGroup->getReferent2()->getFullname() : null,
            'Pôle' => $supportGroup->getService()->getPole()->getName(),
            'Service' => $supportGroup->getService()->getName(),
            'Dispositif' => $supportGroup->getDevice() ? $supportGroup->getDevice()->getName() : '',
            'Nom du logement/ hébergement' => join(', ', $nameAccommodations),
            'Adresse' => join(', ', $addressAccommodations),
            'Ville' => join(', ', $cityAccommodations),
            'Département' => join(', ', $zipCodeAccommodations),
        ];
    }

    public function formatDate($date)
    {
        return $date ? Date::PHPToExcel($date->format('Y-m-d')) : null;
    }
}
