<?php

namespace App\Service\Export;

use App\Entity\Support\Avdl;
use App\Entity\Support\OriginRequest;
use App\Entity\Support\SupportPerson;
use App\Service\ExportExcel;
use Symfony\Component\HttpFoundation\Response;

class AvdlSupportPersonExport extends ExportExcel
{
    protected $arrayData;

    public function __construct()
    {
        $this->arrayData = [];
    }

    /**
     * Exporte les données.
     *
     * @param SupportPerson[] $supports
     */
    public function exportData(array $supports): Response
    {
        $arrayData = [];
        $i = 0;

        foreach ($supports as $supportPerson) {
            if (0 === $i) {
                $arrayData[] = array_keys($this->getDatas($supportPerson));
            }
            $arrayData[] = $this->getDatas($supportPerson);
            ++$i;
        }

        $this->createSheet($arrayData, [
            'name' => 'export_suivis',
            'columnsWidth' => 15,
        ]);

        return $this->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    public function getDatas(SupportPerson $supportPerson): array
    {
        $person = $supportPerson->getPerson();
        $supportGroup = $supportPerson->getSupportGroup();
        $peopleGroup = $supportGroup->getPeopleGroup();
        $originRequest = $supportGroup->getOriginRequest() ?? new OriginRequest();
        $avdlSupport = $supportGroup->getAvdl() ?? new Avdl();

        $datas = [
            'N° suivi' => $supportGroup->getId(),
            'Nom' => $person->getLastname(),
            'Prénom' => $person->getFirstname(),
            'Date de naissance' => $this->formatDate($person->getBirthdate()),
            'Âge' => (string) $person->getAge(),
            'Typologie familiale' => $peopleGroup->getFamilyTypologyToString(),
            'Nb de personnes' => $peopleGroup->getNbPeople(),
            'Rôle dans le groupe' => $supportPerson->getRoleToString(),
            'DP' => $supportPerson->getHeadToString(),
            'Statut suivi' => $supportGroup->getStatusToString(),
            'Date début suivi' => $this->formatDate($supportGroup->getStartDate()),
            'Date fin suivi' => $this->formatDate($supportGroup->getEndDate()),
            'Durée suivi (nb de jours)' => $supportGroup->getDuration(),
            'Coefficient' => $supportGroup->getCoefficient(),
            'Dispositif' => $supportGroup->getDevice() ? $supportGroup->getDevice()->getName() : '',
            'Intervenant·e principal·e' => $supportGroup->getReferent() ? $supportGroup->getReferent()->getFullname() : null,
            'Intervenant·e suppléant·e' => $supportGroup->getReferent2() ? $supportGroup->getReferent2()->getFullname() : null,
            'Prescripteur/ orienteur' => $originRequest->getOrganization() ? $originRequest->getOrganization()->getName() : null,
            'Précision prescripteur/ orienteur' => $originRequest->getOrganizationComment(),
            'Date de mandatement' => $this->formatDate($originRequest->getOrientationDate()),
            'Ville d\'origine' => $supportGroup->getCity(),
            'Commentaire sur la demande' => $originRequest->getOrganizationComment(),
            'Date de début du diagnostic' => $this->formatDate($avdlSupport->getDiagStartDate()),
            'Date de fin du diagnostic' => $this->formatDate($avdlSupport->getDiagEndDate()),
            'Type de diagnostic' => $avdlSupport->getDiagTypeToString(),
            'Préconisation d\'accompagnement' => $avdlSupport->getRecommendationSupportToString(),
            'Commentaire diagnostic' => $avdlSupport->getDiagComment(),
            'Date de début de l\'accompagnement' => $this->formatDate($avdlSupport->getSupportStartDate()),
            'Date de fin de l\'accompagnement' => $this->formatDate($avdlSupport->getSupportEndDate()),
            'Type d\'accompagnement' => $avdlSupport->getSupportTypeToString(),
            'Commentaire sur l\'accompagnement' => $avdlSupport->getSupportComment(),
            'Motif de fin d\'accompagnement' => $supportGroup->getEndReasonToString(),
            'Type d\'accès au logement' => $avdlSupport->getAccessHousingModalityToString(),
            'Situation à la fin' => $supportGroup->getEndStatusToString(),
            'Commentaire situation à la fin' => $supportGroup->getEndStatusComment(),
            'Date de la proposition' => $this->formatDate($avdlSupport->getPropoHousingDate()),
            'Résultat de la proposition' => $avdlSupport->getPropoResultToString(),
            'Date d\'accès au logement' => $this->formatDate($avdlSupport->getAccessHousingDate()),
            'Commentaire fin d\'accompagnement ou propo. logement' => $avdlSupport->getEndSupportComment(),
        ];

        return $datas;
    }
}
