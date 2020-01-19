<?php

namespace App\Export;

use App\Entity\Person;
use App\Service\Export;

use App\Service\ObjectToArray;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PersonExport
{
    protected $arrayData;

    /**
     * @var Person $person
     */
    protected $person;

    public function __construct()
    {
        $this->arrayData = [];
        $this->objectToArray = new ObjectToArray();
    }

    /**
     * Exporte les données
     */
    public function exportData($persons)
    {
        $headers = $this->getHeaders();
        $this->arrayData[] = $headers;

        foreach ($persons as $person) {
            $this->person = $person;

            $row = $this->getRow();

            $this->arrayData[] = $row;
        }

        $alphas = range("A", "Z");
        $columnsWithDate = [];
        foreach ($headers as $key => $value) {
            if (stristr($value, "Date"))
                $columnsWithDate[] = $alphas[$key];
        }

        $export = new Export("export_utilisateurs", "xlsx", $this->arrayData,  $columnsWithDate, null);

        return $export->exportFile();
    }

    /**
     *  Retourne les entêtes du tableau
     */
    protected function getHeaders()
    {
        $headers = [
            "N° utilisateur",
            "Nom",
            "Prénom",
            "Date de naissance",
            "Sexe",
            "Typologie familiale",
            "Nb de personnes",
            "Rôle dans le groupe",
            "Date de création",
            "Date de mise à jour",
            "Typologie familiale",
            "Nb de personnes"
        ];

        return $headers;
    }

    /**
     * Retourne une ligne de résultats
     */
    protected function getRow()
    {
        $typologies = [];
        $nbPeople = [];
        $roles = [];
        foreach ($this->person->getRolesPerson() as $roleUser) {
            $group = $roleUser->getGroupPeople();
            $typologies[] = $group->getFamilyTypologyType();
            $nbPeople[] = $group->getNbPeople();
            $roles[] = $roleUser->getRoleList();
        }

        return [
            $this->person->getId(),
            $this->person->getLastname(),
            $this->person->getFirstname(),
            Date::PHPToExcel($this->person->getBirthdate()->format("d/m/Y")),
            $this->person->getGenderList(),
            join($typologies, ", "),
            join($nbPeople, ", "),
            join($roles, ", "),
            Date::PHPToExcel($this->person->getCreatedAt()->format("d/m/Y")),
            Date::PHPToExcel($this->person->getUpdatedAt()->format("d/m/Y")),
        ];
    }
}
