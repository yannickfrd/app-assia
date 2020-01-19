<?php

namespace App\Export;

use App\Entity\SitSocial;
use App\Entity\SitAdm;
use App\Entity\SitFamilyGroup;
use App\Entity\SitProf;
use App\Entity\SitBudgetGroup;
use App\Entity\SitBudget;
use App\Entity\SitFamilyPerson;
use App\Entity\SitHousing;

use App\Service\Export;
use App\Service\ObjectToArray;

use PhpOffice\PhpSpreadsheet\Shared\Date;

class SupportPersonFullExport
{
    protected $arrayData;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var SupportPerson
     */
    protected $supportPerson;

    /**
     * @var SupportGroup
     */
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

        foreach ($supports as $supportPerson) {

            $this->supportPerson = $supportPerson;
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


            $sitSocial = $this->supportGroup->getSitSocial();
            if ($sitSocial) {
                $row = array_merge($row, $this->objectToArray->getValues($sitSocial));
            } else {
                $row = array_merge($row, $this->objectToArray->getValues(new SitSocial));
            }

            $sitAdm = $this->supportPerson->getSitAdm();
            if ($sitAdm) {
                $row = array_merge($row, $this->objectToArray->getValues($sitAdm));
            } else {
                $row = array_merge($row, $this->objectToArray->getValues(new SitAdm));
            }

            $sitFamilyGroup = $this->supportGroup->getSitFamilyGroup();
            if ($sitFamilyGroup) {
                $row = array_merge($row, $this->objectToArray->getValues($sitFamilyGroup));
            } else {
                $row = array_merge($row, $this->objectToArray->getValues(new SitFamilyGroup));
            }

            $sitFamilyPerson = $this->supportPerson->getSitFamilyPerson();
            if ($sitFamilyPerson) {
                $row = array_merge($row, $this->objectToArray->getValues($sitFamilyPerson));
            } else {
                $row = array_merge($row, $this->objectToArray->getValues(new SitFamilyPerson));
            }

            $sitProf = $this->supportPerson->getSitProf();
            if ($sitProf) {
                $row = array_merge($row, $this->objectToArray->getValues($sitProf));
            } else {
                $row = array_merge($row, $this->objectToArray->getValues(new SitProf));
            }

            $sitBudgetGroup = $this->supportGroup->getSitBudgetGroup();
            if ($sitBudgetGroup) {
                $row = array_merge($row, $this->objectToArray->getValues($sitBudgetGroup));
            } else {
                $row = array_merge($row, $this->objectToArray->getValues(new SitBudgetGroup));
            }

            $sitBudget = $this->supportPerson->getSitBudget();
            if ($sitBudget) {
                $row = array_merge($row, $this->objectToArray->getValues($sitBudget));
            } else {
                $row = array_merge($row, $this->objectToArray->getValues(new SitBudget));
            }

            $sitHousing = $this->supportGroup->getSitHousing();
            if ($sitHousing) {
                $row = array_merge($row, $this->objectToArray->getValues($sitHousing));
            } else {
                $row = array_merge($row, $this->objectToArray->getValues(new SitHousing));
            }

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

        $sitSocial = $this->objectToArray->getKeys(new SitSocial());
        $headers = array_merge($headers, $sitSocial);

        $sitAdm = $this->objectToArray->getKeys(new SitAdm());
        $headers = array_merge($headers, $sitAdm);

        $sitFamilyGroup = $this->objectToArray->getKeys(new SitFamilyGroup());
        $headers = array_merge($headers, $sitFamilyGroup);

        $sitFamilyPerson = $this->objectToArray->getKeys(new SitFamilyPerson());
        $headers = array_merge($headers, $sitFamilyPerson);

        $sitProf = $this->objectToArray->getKeys(new SitProf());
        $headers = array_merge($headers, $sitProf);

        $sitBudgetGroup = $this->objectToArray->getKeys(new SitBudgetGroup());
        $headers = array_merge($headers, $sitBudgetGroup);

        $sitBudget = $this->objectToArray->getKeys(new SitBudget());
        $headers = array_merge($headers, $sitBudget);

        $sitHousing = $this->objectToArray->getKeys(new SitHousing());
        $headers = array_merge($headers, $sitHousing);

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
