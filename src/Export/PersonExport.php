<?php

namespace App\Export;

use App\Entity\Person;
use App\Service\Export;

use PhpOffice\PhpSpreadsheet\Shared\Date;

class PersonExport
{
    /**
     * Exporte les données
     */
    public function exportData($people)
    {
        $arrayData[] = array_keys((array) $this->getDatas($people[0]));

        foreach ($people as $person) {
            $arrayData[] = $this->getDatas($person);
        }

        $export = new Export("export_personnes", "xlsx", $arrayData, null);

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau
     * @param Person $person
     * @return array
     */
    protected function getDatas(Person $person)
    {
        $typologies = [];
        $nbPeople = [];
        $roles = [];
        foreach ($person->getRolesPerson() as $roleUser) {
            $groupPeople = $roleUser->getGroupPeople();
            $typologies[] = $groupPeople->getFamilyTypologyType();
            $nbPeople[] = $groupPeople->getNbPeople();
            $roles[] = $roleUser->getRoleList();
        }

        return [
            "N° utilisateur" => $person->getId(),
            "Nom" => $person->getLastname(),
            "Prénom" => $person->getFirstname(),
            "Date de naissance" => Date::PHPToExcel($person->getBirthdate()->format("d/m/Y")),
            "Sexe" => $person->getGenderList(),
            "Typologie familiale" => join($typologies, ", "),
            "Nb de personnes" => join($nbPeople, ", "),
            "Rôle dans le groupe" => join($roles, ", "),
            "Date de création" => Date::PHPToExcel($person->getCreatedAt()->format("d/m/Y")),
            "Date de mise à jour" => Date::PHPToExcel($person->getUpdatedAt()->format("d/m/Y")),
        ];
    }
}
