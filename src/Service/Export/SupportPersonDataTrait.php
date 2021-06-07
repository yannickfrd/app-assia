<?php

namespace App\Service\Export;

use App\Entity\Support\SupportPerson;

trait SupportPersonDataTrait
{
    public function getSupportPersonDatas(SupportPerson $supportPerson, bool $anonymized = false): array
    {
        $person = $supportPerson->getPerson();
        $supportGroup = $supportPerson->getSupportGroup();
        $peopleGroup = $supportGroup->getPeopleGroup();
        $originRequest = $supportGroup->getOriginRequest() ?? $this->originRequest;

        $startPlaces = [];
        $endPlaces = [];
        $endReasonPlaces = [];
        $namePlaces = [];

        foreach ($supportPerson->getPlacesPerson() as $placePerson) {
            $startPlaces[] = $placePerson->getStartDate() ?? null;
            $endPlaces[] = $placePerson->getEndDate() ?? null;
            $placePerson->getEndReason() ? $endReasonPlaces[] = $placePerson->getEndReasonToString() : null;
            $place = $placePerson->getPlaceGroup()->getPlace();
            $namePlaces[] = (string) $place->getName().' ';
        }

        $datas = [
            'N° suivi personne' => $supportPerson->getId(),
            'N° suivi groupe' => $supportGroup->getId(),
            'N° personne' => $person->getId(),
            'N° groupe' => $peopleGroup->getId(),
            'ID groupe SI-SIAO' => (string) $peopleGroup->getSiSiaoId(),
            'Nom' => $anonymized ? 'XXX' : $person->getLastname(),
            'Prénom' => $anonymized ? 'XXX' : $person->getFirstname(),
            'Date de naissance' => $anonymized ? 'XXX' : $this->formatDate($person->getBirthdate()),
            'Âge' => (string) $person->getAge(),
            'Sexe' => $person->getGenderToString(),
            'Typologie familiale' => $peopleGroup->getFamilyTypologyToString(),
            'Nb de personnes' => (string) $supportGroup->getNbPeople(),
            'Nb enfants -3 ans' => (string) $supportGroup->getNbChildrenUnder3years(),
            'Rôle dans le groupe' => $supportPerson->getRoleToString(),
            'DP' => $supportPerson->getHeadToString(),
            'Statut suivi' => $supportPerson->getStatusToString(),
            'Coefficient' => $supportGroup->getCoefficient(),
            'Date début suivi' => $this->formatDate($supportPerson->getStartDate()),
            'Date fin théorique suivi' => $this->formatDate($supportGroup->getTheoreticalEndDate()),
            'Date fin suivi' => $this->formatDate($supportPerson->getEndDate()),
            'Situation à la fin' => $supportPerson->getEndStatusToString(),
            'Commentaire situation à la fin' => $anonymized ? 'XXX' : $supportPerson->getEndStatusComment(),
            'Fin - Ville' => $supportGroup->getEndLocationCity(),
            'Fin - Département' => (string) $supportGroup->getEndLocationDept(),
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
            'Adresse' => $anonymized ? 'XXX' : $supportGroup->getAddress(),
            'Ville' => $supportGroup->getCity(),
            'Département' => (string) $supportGroup->getDept(),
            // 'Statut suivi (groupe)' => $supportGroup->getStatusToString(),
            // 'Date début suivi (groupe)' => $this->formatDate($supportGroup->getStartDate()),
            // 'Date fin théorique suivi (groupe)' => $this->formatDate($supportGroup->getTheoreticalEndDate()),
            // 'Date fin suivi (groupe)' => $this->formatDate($supportGroup->getEndDate()),
            // 'Situation à la fin (groupe)' => $supportGroup->getEndStatusToString(),
            // 'Commentaire situation à la fin (groupe)' => $anonymized ? 'XXX' : $supportGroup->getEndStatusComment(),
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
