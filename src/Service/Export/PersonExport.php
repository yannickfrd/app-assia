<?php

namespace App\Service\Export;

use App\Entity\Person;
use App\Service\ExportExcel;

class PersonExport extends ExportExcel
{
    /**
     * Exporte les données.
     */
    public function exportData($people)
    {
        $arrayData[] = array_keys((array) $this->getDatas($people[0]));

        foreach ($people as $person) {
            $arrayData[] = $this->getDatas($person);
        }

        $this->createSheet('export_personnes', 'xlsx', $arrayData);

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(Person $person): array
    {
        $typologies = [];
        $nbPeople = [];
        $roles = [];
        foreach ($person->getRolesPerson() as $roleUser) {
            $groupPeople = $roleUser->getGroupPeople();
            $typologies[] = $groupPeople->getFamilyTypologyToString();
            $nbPeople[] = $groupPeople->getNbPeople();
            $roles[] = $roleUser->getRoleToString();
        }

        return [
            'N° utilisateur' => $person->getId(),
            'Nom' => $person->getLastname(),
            'Prénom' => $person->getFirstname(),
            'Date de naissance' => $this->formatDate($person->getBirthdate()),
            'Sexe' => $person->getGenderToString(),
            'Typologie familiale' => join(', ', $typologies),
            'Nb de personnes' => join(', ', $nbPeople),
            'Rôle dans le groupe' => join(', ', $roles),
            'Date de création' => $this->formatDate($person->getCreatedAt()),
            'Date de mise à jour' => $this->formatDate($person->getUpdatedAt()),
        ];
    }
}
