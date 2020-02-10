<?php

namespace App\Export;

use App\Entity\SupportPerson;
use App\Entity\SitSocialGroup;
use App\Entity\SitAdmPerson;
use App\Entity\SitFamilyGroup;
use App\Entity\SitFamilyPerson;
use App\Entity\SitProfPerson;
use App\Entity\SitBudgetGroup;
use App\Entity\SitBudgetPerson;
use App\Entity\SitHousingGroup;
use App\Service\Export;
use App\Service\ObjectToArray;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SupportPersonFullExport
{
    protected $arrayData;
    protected $objectToArray;
    protected $datas;
    protected $sitSocialGroup;
    protected $sitAdmPerson;
    protected $sitFamilyGroup;
    protected $sitProfPerson;
    protected $sitBudgetGroup;
    protected $sitBudgetPerson;
    protected $sitHousingGroup;

    public function __construct(ObjectToArray $objectToArray)
    {
        $this->arrayData = [];
        $this->objectToArray = $objectToArray;
        $this->sitSocialGroup = new SitSocialGroup();
        $this->sitAdmPerson = new SitAdmPerson();
        $this->sitFamilyPerson = new SitFamilyPerson();
        $this->sitFamilyGroup = new SitFamilyGroup();
        $this->sitProfPerson = new SitProfPerson();
        $this->sitBudgetGroup = new SitBudgetGroup();
        $this->sitBudgetPerson = new SitBudgetPerson();
        $this->sitHousingGroup = new SitHousingGroup();
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

        $this->mergeObject($this->sitSocialGroup, $supportGroup->getsitSocialGroup(), "sitSocialGroup", "sitSocial");
        $this->mergeObject($this->sitAdmPerson, $supportPerson->getSitAdmPerson(), "sitAdmPerson", "sitAdm");
        $this->mergeObject($this->sitFamilyGroup, $supportGroup->getSitFamilyGroup(), "sitFamilyGroup", "sitFamily");
        $this->mergeObject($this->sitFamilyPerson, $supportPerson->getSitFamilyPerson(), "sitFamilyPerson", "sitFamily");
        $this->mergeObject($this->sitProfPerson, $supportPerson->getSitProfPerson(), "sitProfPerson", "sitProf");
        $this->mergeObject($this->sitBudgetGroup, $supportGroup->getSitBudgetGroup(), "sitBudgetGroup", "sitBudget");
        $this->mergeObject($this->sitBudgetPerson, $supportPerson->getSitBudgetPerson(), "sitBudgetPerson", "sitBudget");
        $this->mergeObject($this->sitHousingGroup, $supportGroup->getSitHousingGroup(), "sitHousingGroup", "sitHousing");

        return $this->datas;
    }

    protected function mergeObject($nameObject, $emtpyObject, $object, $translation)
    {
        $array = $this->objectToArray->getArray($nameObject, $emtpyObject, $object, $translation);
        $this->datas = array_merge($this->datas, $array);
    }
}
