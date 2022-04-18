<?php

namespace App\Service\Evaluation;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalInitGroup;
use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Evaluation\EvalJusticePerson;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialGroup;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\Service;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Form\Utils\Choices;
use App\Form\Utils\EvaluationChoices;

class EvaluationCompletionChecker
{
    private const YES_OR_IN_PROGRESS = [Choices::YES, EvaluationChoices::IN_PROGRESS];

    private int $points = 0;
    private int $maxPoints = 0;

    private ?Service $service = null;
    private int $nbChildren = 0;

    public function getScore(?EvaluationGroup $evaluationGroup = null): array
    {
        if (!$evaluationGroup) {
            return [
                'points' => 0,
                'score' => 0,
            ];
        }

        $this->reinitProperties();

        $this->service = $evaluationGroup->getSupportGroup()->getService();

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            if (RolePerson::ROLE_CHILD === $evaluationPerson->getSupportPerson()->getRole()) {
                ++$this->nbChildren;
            }
        }

        $this->checkEvalInitGroup($evaluationGroup->getEvalInitGroup());
        $this->checkEvalSocialGroup($evaluationGroup->getEvalSocialGroup());
        $this->checkEvalHousingGroup($evaluationGroup->getEvalHousingGroup());

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            $age = $evaluationPerson->getSupportPerson()->getPerson()->getAge();
            $role = $evaluationPerson->getSupportPerson()->getRole();

            $this->checkEvalInitPerson($evaluationPerson->getEvalInitPerson(), $age);
            $this->checkEvalAdmPerson($evaluationPerson->getEvalAdmPerson());
            $this->checkEvalFamilyPerson($evaluationPerson->getEvalFamilyPerson(), $role);
            $this->checkEvalSocialPerson($evaluationPerson->getEvalSocialPerson(), $role);

            if ($age >= 16) {
                $this->checkEvalProfPerson($evaluationPerson->getEvalProfPerson());
                $this->checkEvalBudgetPerson($evaluationPerson->getEvalBudgetPerson());
            }

            if (Choices::YES === $this->service->getJustice()
                && true === $evaluationPerson->getSupportPerson()->getHead()) {
                $this->checkEvalJusticePerson($evaluationPerson->getEvalJusticePerson());
            }
        }

        return [
            'points' => $this->points,
            'score' => round(($this->points / $this->maxPoints) * 100),
        ];
    }

    private function checkEvalInitGroup(?EvalInitGroup $evalInitGroup = null): void
    {
        if (null === $evalInitGroup) {
            $this->maxPoints += 2;

            return;
        }

        $this->isFilled($evalInitGroup->getSiaoRequest(), 'siaoRequest');
        $this->isFilled($evalInitGroup->getSocialHousingRequest(), 'socialHousingRequest');
    }

    private function checkEvalSocialGroup(?EvalSocialGroup $evalSocialGroup = null): void
    {
        if (null === $evalSocialGroup) {
            $this->maxPoints += 2;

            return;
        }

        $this->isFilled($evalSocialGroup->getWanderingTime(), 'wanderingTime');
        $this->isFilled($evalSocialGroup->getReasonRequest(), 'reasonRequest');
    }

    private function checkEvalHousingGroup(?EvalHousingGroup $evalHousingGroup = null): void
    {
        if (null === $evalHousingGroup) {
            $this->maxPoints += 5;

            return;
        }

        if ($this->isFilled($evalHousingGroup->getSiaoRequest(), 'siaoRequest', self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalHousingGroup->getSiaoRequestDate(),
                $evalHousingGroup->getSiaoUpdatedRequestDate(),
                $evalHousingGroup->getSiaoRequestDept(),
                $evalHousingGroup->getSiaoRecommendation(),
            ], 'siaoRequest');
        }

        if ($this->isFilled($evalHousingGroup->getSocialHousingRequest(), 'socialHousingRequest', self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalHousingGroup->getSocialHousingRequestId(),
                $evalHousingGroup->getSocialHousingRequestDate(),
                $evalHousingGroup->getSocialHousingUpdatedRequestDate(),
            ], 'socialHousingRequest');
        }

        if ($this->isFilled($evalHousingGroup->getSyplo(), 'syplo', self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalHousingGroup->getSyploDate(),
                $evalHousingGroup->getSyploId(),
            ], 'syplo');
        }

        if ($this->isFilled($evalHousingGroup->getDaloAction(), 'daloAction', self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalHousingGroup->getDaloType(),
                $evalHousingGroup->getDaloTribunalAction(),
            ], 'daloAction');
        }

        if ($this->isFilled($evalHousingGroup->getDomiciliation(), 'domiciliation', self::YES_OR_IN_PROGRESS)) {
            if ($this->isFilled($evalHousingGroup->getDomiciliationType(), 'domiciliationType', [1, 2])) {
                $this->checkValues([
                    $evalHousingGroup->getDomiciliationDept(),
                    $evalHousingGroup->getEndDomiciliationDate(),
                ], 'domiciliationType');
            }
        }
    }

    private function checkEvalInitPerson(?EvalInitPerson $evalInitPerson = null, int $age = null): void
    {
        if (null === $evalInitPerson) {
            $this->maxPoints += 4;

            return;
        }

        if ($this->isFilled($evalInitPerson->getPaper(), 'paper', self::YES_OR_IN_PROGRESS)) {
            $this->isFilled($evalInitPerson->getPaperType(), 'paperType');
        }

        if ($age < 16) {
            return;
        }

        $this->isFilled($evalInitPerson->getProfStatus(), 'profStatus');

        if ($this->isFilled($evalInitPerson->getRightSocialSecurity(), 'rightSocialSecurity', self::YES_OR_IN_PROGRESS)) {
            $this->isFilled($evalInitPerson->getSocialSecurity(), 'socialSecurity');
        }

        if ($this->isFilled($evalInitPerson->getResource(), 'resource', self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalInitPerson->getEvalBudgetResources()->count(),
                $evalInitPerson->getResourcesAmt(),
            ], 'resource');
        }

        if (Choices::YES === $this->isFilled($evalInitPerson->getDebt(), 'debt')) {
            $this->isFilled($evalInitPerson->getDebtsAmt(), 'debtsAmt');
        }
    }

    private function checkEvalAdmPerson(?EvalAdmPerson $evalAdmPerson = null): void
    {
        if (null === $evalAdmPerson) {
            $this->maxPoints += 2;

            return;
        }

        if ($this->isFilled(
            $evalAdmPerson->getNationality(),
            'nationality',
            [EvalAdmPerson::NATIONALITY_EU, EvalAdmPerson::NATIONALITY_OUTSIDE_EU])
        ) {
            if (Choices::YES === $this->isFilled($evalAdmPerson->getAsylumBackground(), 'asylumBackground')) {
                $this->isFilled($evalAdmPerson->getAsylumStatus(), 'asylumStatus');
            }
        }

        if ($this->isFilled($evalAdmPerson->getPaper(), 'paper', self::YES_OR_IN_PROGRESS)) {
            $this->isFilled($evalAdmPerson->getPaperType(), 'paperType');
        }
    }

    private function checkEvalFamilyPerson(?EvalFamilyPerson $evalFamilyPerson = null, int $role): void
    {
        if (null === $evalFamilyPerson) {
            $this->maxPoints += 2;

            return;
        }

        $supportPerson = $evalFamilyPerson->getEvaluationPerson()->getSupportPerson();

        if (RolePerson::ROLE_CHILD === $role) {
            if (Choices::YES === $this->isFilled($evalFamilyPerson->getChildcareOrSchool(), 'childcareOrSchool')) {
                $this->isFilled($evalFamilyPerson->getChildcareSchoolType(), 'childcareSchoolType');
            }

            $this->isFilled($evalFamilyPerson->getPmiFollowUp(), 'pmiFollowUp');
        } else {
            if (6 === $this->isFilled($evalFamilyPerson->getMaritalStatus(), 'maritalStatus')) {
                $this->isFilled($evalFamilyPerson->getNoConciliationOrder(), 'noConciliationOrder');
            }

            if (Person::GENDER_FEMALE === $supportPerson->getPerson()->getGender()) {
                if (Choices::YES === $this->isFilled($evalFamilyPerson->getUnbornChild(), 'unbornChild')) {
                    $this->checkValues([
                        $evalFamilyPerson->getExpDateChildbirth(),
                        $evalFamilyPerson->getPregnancyType(),
                    ], 'unbornChild');
                }

                if ($this->nbChildren > 0) {
                    $this->isFilled($evalFamilyPerson->getPmiFollowUp(), 'pmiFollowUp');
                }
            }
        }
    }

    private function checkEvalSocialPerson(?EvalSocialPerson $evalSocialPerson = null, int $role): void
    {
        if (null === $evalSocialPerson) {
            $this->maxPoints += 4;

            return;
        }

        if ($this->isFilled($evalSocialPerson->getRightSocialSecurity(), 'rightSocialSecurity', self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalSocialPerson->getRightSocialSecurity(),
                $evalSocialPerson->getEndRightsSocialSecurityDate(),
            ], 'rightSocialSecurity');
        }

        if ($this->nbChildren > 0) {
            if (Choices::YES === $this->isFilled($evalSocialPerson->getAseFollowUp(), 'aseFollowUp')) {
                $this->isFilled($evalSocialPerson->getAseMeasureType(), 'aseMeasureType');
            }
        }

        if (Choices::YES === $this->isFilled($evalSocialPerson->getHealthProblem(), 'healthProblem')) {
            $this->isFilled($evalSocialPerson->getMedicalFollowUp(), 'medicalFollowUp');
        }

        if (RolePerson::ROLE_CHILD !== $role) {
            // $this->checkValues([
            //     $evalSocialPerson->getFamilyBreakdown(),
            //     $evalSocialPerson->getFriendshipBreakdown(),
            // ]);
            if (Choices::YES === $this->isFilled($evalSocialPerson->getViolenceVictim(), 'violenceVictim')) {
                $this->isFilled($evalSocialPerson->getDomViolenceVictim(), 'domViolenceVictim');
            }
        }
    }

    private function checkEvalProfPerson(?EvalProfPerson $evalProfPerson = null): void
    {
        if (null === $evalProfPerson) {
            $this->maxPoints += 2;

            return;
        }

        $this->isFilled($evalProfPerson->getProfStatus(), 'profStatus');

        if (EvalProfPerson::PROF_STATUS_EMPLOYEE === $evalProfPerson->getProfStatus()) {
            $this->checkValues([
                $evalProfPerson->getContractType(),
                $evalProfPerson->getWorkingTime(),
            ], 'profStatus');
        }

        if (in_array($evalProfPerson->getProfStatus(), [1, 2, 3, 4, 8, 9, 97])) {
            if (Choices::YES === $this->isFilled($evalProfPerson->getRqth(), 'rqth')) {
                $this->isFilled($evalProfPerson->getEndRqthDate(), 'endRqthDate');
            }
        }
    }

    private function checkEvalBudgetPerson(?EvalBudgetPerson $evalBudgetPerson = null): void
    {
        if (null === $evalBudgetPerson) {
            $this->maxPoints += 4;

            return;
        }

        if ($this->isFilled($evalBudgetPerson->getResource(), 'resource', self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalBudgetPerson->getEvalBudgetResources()->count(),
                $evalBudgetPerson->getResourcesAmt(),
                $evalBudgetPerson->getIncomeTax(),
            ], 'resource');
        }

        if (Choices::YES === $this->isFilled($evalBudgetPerson->getCharge(), 'charge')) {
            $this->checkValues([
                $evalBudgetPerson->getEvalBudgetCharges()->count(),
                $evalBudgetPerson->getChargesAmt(),
            ], 'charge');
        }

        if (Choices::YES === $this->isFilled($evalBudgetPerson->getDebt(), 'debt')) {
            $this->checkValues([
                $evalBudgetPerson->getEvalBudgetDebts()->count(),
                $evalBudgetPerson->getDebtsAmt(),
                $evalBudgetPerson->getOverIndebtRecord(),
                $evalBudgetPerson->getSettlementPlan(),
                $evalBudgetPerson->getMoratorium(),
            ], 'debt');
        }
    }

    private function checkEvalJusticePerson(?EvalJusticePerson $evalJusticePerson = null): void
    {
        if (null === $evalJusticePerson) {
            $this->maxPoints += 2;

            return;
        }

        $this->isFilled($evalJusticePerson->getJusticeStatus(), 'justiceStatus');
        $this->isFilled($evalJusticePerson->getJusticeAct(), 'justiceAct');
    }

    private function reinitProperties(): void
    {
        $this->maxPoints = 0;
        $this->points = 0;
        $this->service = null;
        $this->nbChildren = 0;
    }

    private function checkValues(array $values, ?string $name = null): void
    {
        foreach ($values as $value) {
            $this->isFilled($value, $name);
        }
    }

    /**
     * @param int|string|null $value
     */
    private function isFilled($value, ?string $name = null, ?array $choices = null): bool
    {
        ++$this->maxPoints;

        if ((is_integer($value) && !in_array($value, [Choices::NO_INFORMATION, null]))
            || (null !== $value)) {
            ++$this->points;

            if ($choices) {
                return in_array($value, $choices, true);
            }

            return true;
        }

        return false;
    }
}
