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

        $placesStartDates = [];
        $placesEndDates = [];
        $placesEndReasons = [];
        $placesNames = [];

        foreach ($supportPerson->getPlacesPerson() as $placePerson) {
            $placesStartDates[] = $placePerson->getStartDate();
            $placesEndDates[] = $placePerson->getEndDate();
            $placePerson->getEndReason() ? $placesEndReasons[] = $placePerson->getEndReasonToString() : null;
            $place = $placePerson->getPlaceGroup()->getPlace();
            $placesNames[] = (string) $place->getName().' ';
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
            'Motif de fin de suivi' => $supportPerson->getEndReasonToString(),
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
            'Date début hébergement' => $placesStartDates ? $this->formatDate(min($placesStartDates)) : null,
            'Date fin hébergement' => $placesEndDates ? $this->formatDate($this->getEndDate($placesEndDates)) : null,
            'Motif fin hébergement' => join(', ', $placesEndReasons),
            'Nom du logement/ hébergement' => (string) join(', ', $placesNames),
            'Adresse' => $anonymized ? 'XXX' : $supportGroup->getAddress(),
            'Ville' => $supportGroup->getCity(),
            'Département' => (string) $supportGroup->getDept(),
            'Prescripteur/ orienteur' => $originRequest->getOrganization() ? $originRequest->getOrganization()->getName() : null,
            'Précision prescripteur/ orienteur' => $originRequest->getOrganizationComment(),
            'Date orientation' => $this->formatDate($originRequest->getOrientationDate()),
            'Date entretien pré-admission' => $this->formatDate($originRequest->getPreAdmissionDate()),
            'Résultat de l\'orientation ou pré-admission' => $originRequest->getResulPreAdmissionToString(),
            'Date décision' => $this->formatDate($originRequest->getDecisionDate()),
        ];

        return $datas;
    }

    /**
     * @param \DateTimeInterface[] $dates
     */
    protected function getEndDate(array $dates): ?\DateTimeInterface
    {
        if (!$dates) {
            return null;
        }

        $minDate = min($dates);

        return null === $minDate ? $minDate : max($dates);
    }
}
