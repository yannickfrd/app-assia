<?php

namespace App\Service\Export;

use App\Entity\HotelSupport;
use App\Entity\OriginRequest;
use App\Entity\SupportPerson;
use App\Service\ExportExcel;

class HotelSupportPersonExport extends ExportExcel
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

        $this->createSheet('export_suivis_hotel', 'xlsx', $arrayData, 15);

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    public function getDatas(SupportPerson $supportPerson): array
    {
        $person = $supportPerson->getPerson();
        $supportGroup = $supportPerson->getSupportGroup();
        $groupPeople = $supportGroup->getGroupPeople();
        $originRequest = $supportGroup->getOriginRequest() ?? new OriginRequest();
        $hotelSupport = $supportGroup->getHotelSupport() ?? new HotelSupport();
        $accommodationGroup = $supportGroup->getAccommodationGroups()->first();
        $datas = [
            'N° Suivi' => $supportGroup->getId(),
            'ID personne' => $person->getId(),
            'Nom' => $person->getLastname(),
            'Prénom' => $person->getFirstname(),
            'Date de naissance' => $this->formatDate($person->getBirthdate()),
            'Âge' => $person->getAge(),
            'Typologie familiale' => $groupPeople->getFamilyTypologyToString(),
            'Nb de personnes' => $groupPeople->getNbPeople(),
            'Rôle dans le groupe' => $supportPerson->getRoleToString(),
            'DP' => $supportPerson->getHeadToString(),
            'Date début suivi' => $this->formatDate($supportGroup->getStartDate()),
            'Date fin suivi' => $this->formatDate($supportGroup->getEndDate()),
            'Statut suivi' => $supportGroup->getStatusToString(),
            'Coefficient' => $supportGroup->getCoefficient(),
            'Dispositif' => $supportGroup->getDevice() ? $supportGroup->getDevice()->getName() : '',
            'Référent social' => $supportGroup->getReferent() ? $supportGroup->getReferent()->getFullname() : null,
            'Référent suppléant' => $supportGroup->getReferent2() ? $supportGroup->getReferent2()->getFullname() : null,
            'Prescripteur/ orienteur' => $originRequest->getOrganization() ? $originRequest->getOrganization()->getName() : null,
            'SSD orienteur' => $hotelSupport->getSsd(),
            'Précision prescripteur/ orienteur' => $originRequest->getOrganizationComment(),
            'Date de la demande' => $this->formatDate($originRequest->getOrientationDate()),
            'Date entrée à l\'hôtel' => $this->formatDate($hotelSupport->getEntryHotelDate()),
            'Département d\'origine' => $hotelSupport->getOriginDeptToString(),
            'Identifiant GIP' => $hotelSupport->getGipId(),
            'Hôtel' => $accommodationGroup && $accommodationGroup->getAccommodation() ? $accommodationGroup->getAccommodation()->getName() : null,
            'Adresse' => $supportGroup->getAddress(),
            'Commune' => $supportGroup->getCity(),
            'Commentaire sur la demande' => $originRequest->getOrganizationComment(),
            'Date de début de l\'accompagnement' => $this->formatDate($supportGroup->getStartDate()),
            'Date de l\'évaluation' => $this->formatDate($hotelSupport->getEvaluationDate()),
            'Date de signature convention' => $this->formatDate($hotelSupport->getAgreementDate()),
            'Département d\'ancrage' => $hotelSupport->getDepartmentAnchorToString(),
            'Préconisation d\'accompagnement' => $hotelSupport->getRecommendationToString(),
            'Date de fin de l\'accompagnement' => $this->formatDate($supportGroup->getEndDate()),
            'Niveau d\'intervention' => $hotelSupport->getLevelSupportToString(),
            'Motif de fin d\'accompagnement' => $hotelSupport->getEndSupportReasonToString(),
            'Situation à la fin' => $supportGroup->getEndStatusToString(),
            'Commentaire situation à la fin' => $supportGroup->getEndStatusComment(),
            'Commentaire sur l\'accompagnement' => $supportGroup->getComment(),
        ];

        return $datas;
    }
}
