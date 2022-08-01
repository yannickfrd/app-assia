<?php

declare(strict_types=1);

namespace App\Service\SupportGroup;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;

class SupportAnonymizer
{
    public function anonymize(SupportGroup $supportGroup): void
    {
        $peopleGroup = $supportGroup->getPeopleGroup();

        $peopleGroup
            ->setSiSiaoId(null)
        ;

        foreach ($peopleGroup->getPeople() as $person) {
            $person
                ->setFirstname('XXX')
                ->setLastname('XXX')
                ->setSiSiaoId(null)
                ->setContactOtherPerson(null)
                ->setPhone1(null)
                ->setPhone2(null)
                ->setEmail(null)
            ;
        }

        $this->anonymizeSupportGroup($supportGroup);
    }

    private function anonymizeSupportGroup(SupportGroup $supportGroup): void
    {
        $supportGroup
            ->setAddress(null)
            ->setComment(null)
            ->setCommentLocation(null)
            ->setEndStatusComment(null)
        ;

        if ($originRequest = $supportGroup->getOriginRequest()) {
            $originRequest
                ->setComment(null)
                ->setOrganizationComment(null)
            ;
        }

        if ($avdl = $supportGroup->getAvdl()) {
            $avdl
                ->setDiagComment(null)
                ->setEndSupportComment(null)
                ->setSupportComment(null)
            ;
        }

        if ($hotelSupport = $supportGroup->getHotelSupport()) {
            $hotelSupport
                ->setEndSupportComment(null)
            ;
        }

        foreach ($supportGroup->getPlaceGroups() as $placeGroup) {
            $this->anonymizePlaceGroup($placeGroup);
        }

        foreach ($supportGroup->getEvaluationsGroup() as $evaluationGroup) {
            $this->anonymizeEvaluationGroup($evaluationGroup);
        }
    }

    private function anonymizePlaceGroup(PlaceGroup $placeGroup): void
    {
        $placeGroup
            ->setComment(null)
            ->setCommentEndReason(null)
        ;

        foreach ($placeGroup->getPlacePeople() as $placePerson) {
            $placePerson
                ->setCommentEndReason(null)
            ;
        }
    }

    private function anonymizeEvaluationGroup(EvaluationGroup $evaluationGroup): void
    {
        $evaluationGroup
            ->setBackgroundPeople(null)
            ->setConclusion(null)
        ;

        if ($evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup()) {
            $evalBudgetGroup
                ->setCafId(null)
                ->setCafAttachment(null)
                ->setCommentEvalBudget(null)
            ;
        }

        if ($evalFamilyGroup = $evaluationGroup->getEvalFamilyGroup()) {
            $evalFamilyGroup
                ->setCommentEvalFamilyGroup(null)
            ;
        }

        if ($evalHotelLifeGroup = $evaluationGroup->getEvalHotelLifeGroup()) {
            $evalHotelLifeGroup
                ->setClothing(null)
                ->setFood(null)
                ->setOtherHotelLife(null)
                ->setRoomMaintenance(null)
                ->setCommentHotelLife(null)
            ;
        }

        if ($evalHousingGroup = $evaluationGroup->getEvalHousingGroup()) {
            $evalHousingGroup
                ->setCommentEvalHousing(null)
                ->setDaloId(null)
                ->setDomiciliationAddress(null)
                ->setDomiciliationComment(null)
                ->setExpulsionComment(null)
                ->setHousingAddress(null)
                ->setHousingExpeComment(null)
                ->setHsgActionRecordId(null)
                ->setSocialHousingRequestId(null)
                ->setSyploId(null)
            ;
        }

        if ($evalSocialGroup = $evaluationGroup->getEvalSocialGroup()) {
            $evalSocialGroup
                ->setAnimalType(null)
                ->setCommentEvalSocialGroup(null)
            ;
        }

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            $this->anonymizeEvaluationPerson($evaluationPerson);
        }
    }

    private function anonymizeEvaluationPerson(EvaluationPerson $evaluationPerson)
    {
        if ($evalAdminPerson = $evaluationPerson->getEvalAdmPerson()) {
            $evalAdminPerson
                ->setAgdrefId(null)
                ->setCndaId(null)
                ->setCommentEvalAdmPerson(null)
                ->setOfpraRegistrationId(null)
            ;
        }

        if ($evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson()) {
            $evalBudgetPerson
                ->setResourcesComment(null)
                ->setChargeComment(null)
                ->setDebtComment(null)
                ->setCommentEvalBudget(null)
            ;
        }

        if ($evalFamilyPerson = $evaluationPerson->getEvalFamilyPerson()) {
            $evalFamilyPerson
                ->setCommentEvalFamilyPerson(null)
                ->setPmiName(null)
                ->setSchoolAddress(null)
                ->setSchoolComment(null)
            ;
        }

        if ($evalJusticePerson = $evaluationPerson->getEvalJusticePerson()) {
            $evalJusticePerson
                ->setCommentEvalJustice(null)
            ;
        }

        if ($evalProfPerson = $evaluationPerson->getEvalProfPerson()) {
            $evalProfPerson
                ->setCommentEvalProf(null)
                ->setEmployerName(null)
                ->setJobCenterId(null)
                ->setJobType(null)
                ->setNbWorkingHours(null)
                ->setTransportMeans(null)
                ->setWorkingHours(null)
                ->setWorkPlace(null)
            ;
        }

        if ($evalSocialPerson = $evaluationPerson->getEvalSocialPerson()) {
            $evalSocialPerson
                ->setSocialSecurityOffice(null)
                ->setAseComment(null)
                ->setInfoCripComment(null)
                ->setCommentEvalSocialPerson(null)
            ;
        }
    }
}
