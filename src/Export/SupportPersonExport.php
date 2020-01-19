<?php

namespace App\Export;

use App\Entity\SitHousing;

use App\Service\Export;
use App\Service\ObjectToArray;

use PhpOffice\PhpSpreadsheet\Shared\Date;

class SupportPersonExport
{
    protected $arrayData;
    protected $person;
    protected $supportPerson;
    protected $supportGroup;
    protected $groupPeople;
    protected $rolePerson;

    public function __construct()
    {
        $this->arrayData = [];
        $this->objectToArray = new ObjectToArray();
    }

    /**
     * Exporte les données
     */
    public function exportData($supports)
    {
        $headers = $this->getHeaders();
        $this->arrayData[] = $headers;

        foreach ($supports as $this->supportPerson) {

            $this->person = $this->supportPerson->getPerson();
            $this->supportGroup = $this->supportPerson->getSupportGroup();
            $this->groupPeople = $this->supportGroup->getGroupPeople();
            $this->rolePerson = null;

            foreach ($this->person->getRolesPerson() as $role) {
                if ($role->getGroupPeople() == $this->groupPeople) {
                    $this->rolePerson = $role;
                }
            }

            $row = $this->getRow();

            // $sitHousing = $this->supportGroup->getSitHousing();
            // if ($sitHousing) {
            //     $row = array_merge($row, $this->objectToArray->getValues($sitHousing));
            // }

            $this->arrayData[] = $row;
        }

        $export = new Export("export_suivis", "xlsx", $this->arrayData, 12.5);

        return $export->exportFile();
    }

    /**
     *  Retourne les entêtes du tableau
     */
    protected function getHeaders()
    {
        $headers = [
            "N° Suivi groupe",
            "N° Suivi personne",
            "N° Groupe",
            "N° Personne",
            "Nom",
            "Prénom",
            "Date de naissance",
            "Typologie familiale",
            "Nb de personnes",
            "Rôle dans le groupe",
            "DP",
            "Statut",
            "Date début suivi",
            "Date Fin suivi",
            "Référent social",
            "Service",
            "Pôle"
        ];

        // $sitHousing = $this->objectToArray->getKeys(new SitHousing());
        // $headers = array_merge($headers, $sitHousing);

        return $headers;
    }

    /**
     * Retourne une ligne de résultats
     */
    protected function getRow()
    {
        return [
            $this->supportGroup->getId(),
            $this->supportPerson->getId(),
            $this->groupPeople->getId(),
            $this->person->getId(),
            $this->person->getLastname(),
            $this->person->getFirstname(),
            Date::PHPToExcel($this->person->getBirthdate()->format("Y-m-d")),
            $this->groupPeople->getFamilyTypologyType(),
            $this->groupPeople->getNbPeople(),
            $this->rolePerson->getRoleList(),
            $this->rolePerson->getHead() ? "Oui" : "Non",
            $this->supportPerson->getStatusType(),
            Date::PHPToExcel($this->supportPerson->getStartDate()->format("Y-m-d")),
            $this->supportPerson->getEndDate() ?  Date::PHPToExcel($this->supportPerson->getEndDate()->format("Y-m-d")) : null,
            $this->supportGroup->getReferent()->getFullname(),
            $this->supportGroup->getService()->getName(),
            $this->supportGroup->getService()->getPole()->getName(),
        ];
    }
}
