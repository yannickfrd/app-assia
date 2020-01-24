<?php

namespace App\Export;

use App\Service\Export;
use App\Service\ObjectToArray;
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
     * Exporte les données
     */
    public function exportData($supports)
    {
        $i = 0;
        foreach ($supports as $supportPerson) {
            if ($i == 0) {
                $arrayData[] = array_keys($this->getDatas($supportPerson));
            }
            $arrayData[] = $this->getDatas($supportPerson);
            $i++;
        }

        $export = new Export("export_suivis", "xlsx", $arrayData, 12.5);

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau
     * @param SupportPerson $supportPerson
     * @return array
     */
    protected function getDatas(SupportPerson $supportPerson)
    {
        $person = $supportPerson->getPerson();
        $supportGroup = $supportPerson->getSupportGroup();
        $groupPeople = $supportGroup->getGroupPeople();
        $rolePerson = null;

        foreach ($person->getRolesPerson() as $role) {
            if ($role->getGroupPeople() == $groupPeople) {
                $rolePerson = $role;
            }
        }

        return [
            "N° Suivi groupe" => $supportGroup->getId(),
            "N° Suivi personne" => $supportPerson->getId(),
            "N° Groupe" => $groupPeople->getId(),
            "N° Personne" => $person->getId(),
            "Nom" => $person->getLastname(),
            "Prénom" => $person->getFirstname(),
            "Date de naissance" => Date::PHPToExcel($person->getBirthdate()->format("Y-m-d")),
            "Typologie familiale" => $groupPeople->getFamilyTypologyType(),
            "Nb de personnes" => $groupPeople->getNbPeople(),
            "Rôle dans le groupe" => $rolePerson->getRoleList(),
            "DP" => $rolePerson->getHead() ? "Oui" : "Non",
            "Statut" => $supportPerson->getStatusType(),
            "Date début suivi" => Date::PHPToExcel($supportPerson->getStartDate()->format("Y-m-d")),
            "Date Fin suivi" => $supportPerson->getEndDate() ?  Date::PHPToExcel($supportPerson->getEndDate()->format("Y-m-d")) : null,
            "Référent social" => $supportGroup->getReferent()->getFullname(),
            "Service" => $supportGroup->getService()->getName(),
            "Pôle" => $supportGroup->getService()->getPole()->getName(),
        ];
    }
}
