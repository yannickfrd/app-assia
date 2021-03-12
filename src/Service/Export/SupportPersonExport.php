<?php

namespace App\Service\Export;

use App\Entity\People\RolePerson;
use App\Entity\Support\OriginRequest;
use App\Entity\Support\SupportPerson;
use App\Service\ExportExcel;

class SupportPersonExport extends ExportExcel
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
    public function exportData(array $supports)
    {
        $arrayData = [];
        $i = 0;

        foreach ($supports as $supportPerson) {
            if (0 === $i) {
                $arrayData[] = array_keys($this->getDatas($supportPerson));
            }
            $arrayData[] = $this->getDatas($supportPerson);
            if ($i > 100) {
                sleep(5);
                $i = 1;
            }
            ++$i;
        }

        $this->createSheet('export_suivis', 'xlsx', $arrayData);

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

        $startPlaces = [];
        $endPlaces = [];
        $endReasonPlaces = [];
        $namePlaces = [];

        foreach ($supportPerson->getPlacesPerson() as $placePerson) {
            $startPlaces[] = $placePerson->getStartDate() ?? null;
            $endPlaces[] = $placePerson->getEndDate() ?? null;
            $placePerson->getEndReason() ? $endReasonPlaces[] = $placePerson->getEndReasonToString() : null;
            $place = $placePerson->getPlaceGroup()->getPlace();
            $namePlaces[] = $place->getName().' ';
        }

        // $nbChildren = 0;

        // if ($supportGroup->getSupportPeople()) {
        //     foreach ($supportGroup->getSupportPeople() as $supportPerson) {
        //         if (RolePerson::ROLE_CHILD === $supportPerson->getRole()) {
        //             ++$nbChildren;
        //         }
        //     }
        // }

        $datas = [
            'N° Groupe' => $peopleGroup->getId(),
            'N° Suivi groupe' => $supportGroup->getId(),
            'N° Personne' => $person->getId(),
            'N° Suivi personne' => $supportPerson->getId(),
            'Nom' => $person->getLastname(),
            'Prénom' => $person->getFirstname(),
            'Date de naissance' => $this->formatDate($person->getBirthdate()),
            'Âge' => (string) $person->getAge(),
            'Sexe' => $person->getGenderToString(),
            'Typologie familiale' => $peopleGroup->getFamilyTypologyToString(),
            'Nb de personnes' => (string) $supportGroup->getNbPeople(),
            // 'Nb d\'enfants' => $nbChildren,
            'Nb enfants -3 ans' => (string) $supportGroup->getNbChildrenUnder3years(),
            'Rôle dans le groupe' => $supportPerson->getRoleToString(),
            'DP' => $supportPerson->getHeadToString(),
            'Statut suivi' => $supportPerson->getStatusToString(),
            'Coefficient' => $supportGroup->getCoefficient(),
            'Date début suivi' => $this->formatDate($supportPerson->getStartDate()),
            'Date fin théorique suivi' => $this->formatDate($supportGroup->getTheoreticalEndDate()),
            'Date fin suivi' => $this->formatDate($supportPerson->getEndDate()),
            'Situation à la fin' => $supportPerson->getEndStatusToString(),
            'Commentaire situation à la fin' => $supportPerson->getEndStatusComment(),
            'Pôle' => $supportGroup->getService()->getPole()->getName(),
            'Service' => $supportGroup->getService()->getName(),
            'Sous-service' => $supportGroup->getSubService() ? $supportGroup->getSubService()->getName() : null,
            'Dispositif' => $supportGroup->getDevice() ? $supportGroup->getDevice()->getName() : '',
            'Référent social' => $supportGroup->getReferent() ? $supportGroup->getReferent()->getFullname() : null,
            'Référent social suppléant' => $supportGroup->getReferent2() ? $supportGroup->getReferent2()->getFullname() : null,
            'Date début hébergement' => $startPlaces ? $this->formatDate(min($startPlaces)) : null,
            'Date fin hébergement' => $endPlaces ? $this->formatDate(max($endPlaces)) : null,
            'Motif fin hébergement' => join(', ', $endReasonPlaces),
            'Nom du logement/ hébergement' => (string) join(', ', $namePlaces),
            'Adresse' => $supportGroup->getAddress(),
            'Ville' => $supportGroup->getCity(),
            'Code postal' => (string) $supportGroup->getZipcode(),
            // 'Statut suivi (groupe)' => $supportGroup->getStatusToString(),
            // 'Date début suivi (groupe)' => $this->formatDate($supportGroup->getStartDate()),
            // 'Date fin théorique suivi (groupe)' => $this->formatDate($supportGroup->getTheoreticalEndDate()),
            // 'Date fin suivi (groupe)' => $this->formatDate($supportGroup->getEndDate()),
            // 'Situation à la fin (groupe)' => $supportGroup->getEndStatusToString(),
            // 'Commentaire situation à la fin (groupe)' => $supportGroup->getEndStatusComment(),
            'Prescripteur/ orienteur' => $originRequest->getOrganization() ? $originRequest->getOrganization()->getName() : null,
            'Précision prescripteur/ orienteur' => $originRequest->getOrganizationComment(),
            'Date orientation' => $this->formatDate($originRequest->getOrientationDate()),
            'Date entretien pré-admission' => $this->formatDate($originRequest->getPreAdmissionDate()),
            'Résultat de l\'orientation ou pré-admission' => $originRequest->getResulPreAdmissionToString(),
            'Date décision' => $this->formatDate($originRequest->getDecisionDate()),
        ];

        return $datas;
    }
}
