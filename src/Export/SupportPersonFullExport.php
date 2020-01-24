<?php

namespace App\Export;

use App\Entity\SupportPerson;
use App\Entity\SitSocial;
use App\Entity\SitAdm;
use App\Entity\SitFamilyGroup;
use App\Entity\SitFamilyPerson;
use App\Entity\SitProf;
use App\Entity\SitBudgetGroup;
use App\Entity\SitBudget;
use App\Entity\SitHousing;
use App\Service\Export;
use App\Service\ObjectToArray;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SupportPersonFullExport
{
    protected $arrayData;
    protected $objectToArray;
    protected $datas;
    protected $sitSocial;
    protected $sitAdm;
    protected $sitFamilyGroup;
    protected $sitProf;
    protected $sitBudgetGroup;
    protected $sitBudget;
    protected $sitHousing;

    public function __construct(ObjectToArray $objectToArray)
    {
        $this->arrayData = [];
        $this->objectToArray = $objectToArray;
        $this->sitSocial = new SitSocial();
        $this->sitAdm = new SitAdm();
        $this->sitFamilyPerson = new SitFamilyPerson();
        $this->sitFamilyGroup = new SitFamilyGroup();
        $this->sitProf = new SitProf();
        $this->sitBudgetGroup = new SitBudgetGroup();
        $this->sitBudget = new SitBudget();
        $this->sitHousing = new SitHousing();
    }

    /**
     * Exporte les données
     */
    public function exportData($supports)
    {
        $arrayData[] = array_keys($this->getDatas($supports[0]));

        foreach ($supports as $supportPerson) {
            $arrayData[] = $this->getDatas($supportPerson);
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

        $this->datas = [
            "N° suivi groupe" => $supportGroup->getId(),
            "N° suivi personne" => $supportPerson->getId(),
            "N° groupe" => $groupPeople->getId(),
            "N° personne" => $person->getId(),
            "Nom" => $person->getLastname(),
            "Prénom" => $person->getFirstname(),
            "Date de naissance" => Date::PHPToExcel($person->getBirthdate()->format("Y-m-d")),
            "Typologie familiale" => $groupPeople->getFamilyTypologyType(),
            "Nb de personnes" => $groupPeople->getNbPeople(),
            "Rôle dans le groupe" => $rolePerson->getRoleList(),
            "DP" => $rolePerson->getHead() ? "Oui" : "Non",
            "Statut" => $supportPerson->getStatusType(),
            "Date début suivi" => Date::PHPToExcel($supportPerson->getStartDate()->format("Y-m-d")),
            "Date fin suivi" => $supportPerson->getEndDate() ?  Date::PHPToExcel($supportPerson->getEndDate()->format("Y-m-d")) : null,
            "Référent social" => $supportGroup->getReferent()->getFullname(),
            "Service" => $supportGroup->getService()->getName(),
            "Pôle" => $supportGroup->getService()->getPole()->getName(),
        ];

        $this->mergeObject($this->sitSocial, $supportGroup->getsitSocial(), "sitSocial");
        $this->mergeObject($this->sitAdm, $supportPerson->getSitAdm(), "sitAdm");
        $this->mergeObject($this->sitFamilyGroup, $supportGroup->getSitFamilyGroup(), "sitFamily");
        $this->mergeObject($this->sitFamilyPerson, $supportPerson->getSitFamilyPerson(), "sitFamilyPerson");
        $this->mergeObject($this->sitProf, $supportPerson->getSitProf(), "sitProf");
        $this->mergeObject($this->sitBudgetGroup, $supportGroup->getSitBudgetGroup(), "sitBudgetGroup");
        $this->mergeObject($this->sitBudget, $supportPerson->getSitBudget(), "sitBudget");
        $this->mergeObject($this->sitHousing, $supportGroup->getSitHousing(), "sitHousing");

        return $this->datas;
    }

    protected function mergeObject($nameObject, $emtpyObject, $object)
    {
        $array = $this->objectToArray->getArray($nameObject, $emtpyObject, $object);
        $this->datas = array_merge($this->datas, $array);
    }
}
