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

        $this->isFilled($evalInitGroup->getSiaoRequest());
        $this->isFilled($evalInitGroup->getSocialHousingRequest());
    }

    private function checkEvalSocialGroup(?EvalSocialGroup $evalSocialGroup = null): void
    {
        if (null === $evalSocialGroup) {
            $this->maxPoints += 2;

            return;
        }

        $this->isFilled($evalSocialGroup->getWanderingTime());
        $this->isFilled($evalSocialGroup->getReasonRequest());
    }

    private function checkEvalHousingGroup(?EvalHousingGroup $evalHousingGroup = null): void
    {
        if (null === $evalHousingGroup) {
            $this->maxPoints += 5;

            return;
        }

        if ($this->isFilled($evalHousingGroup->getSiaoRequest(), self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalHousingGroup->getSiaoRequestDate(),
                $evalHousingGroup->getSiaoUpdatedRequestDate(),
                $evalHousingGroup->getSiaoRequestDept(),
                $evalHousingGroup->getSiaoRecommendation(),
            ]);
        }

        if ($this->isFilled($evalHousingGroup->getSocialHousingRequest(), self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalHousingGroup->getSocialHousingRequestId(),
                $evalHousingGroup->getSocialHousingRequestDate(),
                $evalHousingGroup->getSocialHousingUpdatedRequestDate(),
            ]);
        }

        if ($this->isFilled($evalHousingGroup->getSyplo(), self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalHousingGroup->getSyploDate(),
                $evalHousingGroup->getSyploId(),
            ]);
        }

        if ($this->isFilled($evalHousingGroup->getDaloAction(), self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalHousingGroup->getDaloType(),
                $evalHousingGroup->getDaloTribunalAction(),
            ]);
        }

        if ($this->isFilled($evalHousingGroup->getDomiciliation(), self::YES_OR_IN_PROGRESS)) {
            if ($this->isFilled($evalHousingGroup->getDomiciliationType(), [1, 2])) {
                $this->checkValues([
                    $evalHousingGroup->getDomiciliationDept(),
                    $evalHousingGroup->getEndDomiciliationDate(),
                ]);
            }
        }
    }

    private function checkEvalInitPerson(?EvalInitPerson $evalInitPerson = null, int $age = null): void
    {
        if (null === $evalInitPerson) {
            $this->maxPoints += 4;

            return;
        }

        if ($this->isFilled($evalInitPerson->getPaper(), self::YES_OR_IN_PROGRESS)) {
            $this->isFilled($evalInitPerson->getPaperType());
        }

        if ($age < 16) {
            return;
        }

        $this->isFilled($evalInitPerson->getProfStatus());

        if ($this->isFilled($evalInitPerson->getRightSocialSecurity(), self::YES_OR_IN_PROGRESS)) {
            $this->isFilled($evalInitPerson->getSocialSecurity());
        }

        if ($this->isFilled($evalInitPerson->getResource(), self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalInitPerson->getEvalBudgetResources()->count(),
                $evalInitPerson->getResourcesAmt(),
            ]);
        }

        if (Choices::YES === $this->isFilled($evalInitPerson->getDebt())) {
            $this->isFilled($evalInitPerson->getDebtsAmt());
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
            [EvalAdmPerson::NATIONALITY_EU, EvalAdmPerson::NATIONALITY_OUTSIDE_EU])
        ) {
            if (Choices::YES === $this->isFilled($evalAdmPerson->getAsylumBackground())) {
                $this->isFilled($evalAdmPerson->getAsylumStatus());
            }
        }

        if ($this->isFilled($evalAdmPerson->getPaper(), self::YES_OR_IN_PROGRESS)) {
            $this->isFilled($evalAdmPerson->getPaperType());
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
            if (Choices::YES === $this->isFilled($evalFamilyPerson->getChildcareOrSchool())) {
                $this->isFilled($evalFamilyPerson->getChildcareSchoolType());
            }

            $this->isFilled($evalFamilyPerson->getPmiFollowUp());
        } else {
            if (6 === $this->isFilled($evalFamilyPerson->getMaritalStatus())) {
                $this->isFilled($evalFamilyPerson->getNoConciliationOrder());
            }

            if (Person::GENDER_FEMALE === $supportPerson->getPerson()->getGender()) {
                if (Choices::YES === $this->isFilled($evalFamilyPerson->getUnbornChild())) {
                    $this->checkValues([
                        $evalFamilyPerson->getExpDateChildbirth(),
                        $evalFamilyPerson->getPregnancyType(),
                    ]);
                }

                if ($this->nbChildren > 0) {
                    $this->isFilled($evalFamilyPerson->getPmiFollowUp());
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

        if ($this->isFilled($evalSocialPerson->getRightSocialSecurity(), self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalSocialPerson->getRightSocialSecurity(),
                $evalSocialPerson->getEndRightsSocialSecurityDate(),
            ]);
        }

        if ($this->nbChildren > 0) {
            if (Choices::YES === $this->isFilled($evalSocialPerson->getAseFollowUp())) {
                $this->isFilled($evalSocialPerson->getAseMeasureType());
            }
        }

        if (Choices::YES === $this->isFilled($evalSocialPerson->getHealthProblem())) {
            $this->isFilled($evalSocialPerson->getMedicalFollowUp());
        }

        if (RolePerson::ROLE_CHILD !== $role) {
            // $this->checkValues([
            //     $evalSocialPerson->getFamilyBreakdown(),
            //     $evalSocialPerson->getFriendshipBreakdown(),
            // ]);
            if (Choices::YES === $this->isFilled($evalSocialPerson->getViolenceVictim())) {
                $this->isFilled($evalSocialPerson->getDomViolenceVictim());
            }
        }
    }

    private function checkEvalProfPerson(?EvalProfPerson $evalProfPerson = null): void
    {
        if (null === $evalProfPerson) {
            $this->maxPoints += 2;

            return;
        }

        $this->isFilled($evalProfPerson->getProfStatus());

        if (EvalProfPerson::PROF_STATUS_EMPLOYEE === $evalProfPerson->getProfStatus()) {
            $this->checkValues([
                $evalProfPerson->getContractType(),
                $evalProfPerson->getWorkingTime(),
            ]);
        }

        if (in_array($evalProfPerson->getProfStatus(), [1, 2, 3, 4, 8, 9, 97])) {
            if (Choices::YES === $this->isFilled($evalProfPerson->getRqth())) {
                $this->isFilled($evalProfPerson->getEndRqthDate());
            }
        }
    }

    private function checkEvalBudgetPerson(?EvalBudgetPerson $evalBudgetPerson = null): void
    {
        if (null === $evalBudgetPerson) {
            $this->maxPoints += 4;

            return;
        }

        if ($this->isFilled($evalBudgetPerson->getResource(), self::YES_OR_IN_PROGRESS)) {
            $this->checkValues([
                $evalBudgetPerson->getEvalBudgetResources()->count(),
                $evalBudgetPerson->getResourcesAmt(),
                $evalBudgetPerson->getIncomeTax(),
            ]);
        }

        if (Choices::YES === $this->isFilled($evalBudgetPerson->getCharge())) {
            $this->checkValues([
                $evalBudgetPerson->getEvalBudgetCharges()->count(),
                $evalBudgetPerson->getChargesAmt(),
            ]);
        }

        if (Choices::YES === $this->isFilled($evalBudgetPerson->getDebt())) {
            $this->checkValues([
                $evalBudgetPerson->getEvalBudgetDebts()->count(),
                $evalBudgetPerson->getDebtsAmt(),
                $evalBudgetPerson->getOverIndebtRecord(),
                $evalBudgetPerson->getSettlementPlan(),
                $evalBudgetPerson->getMoratorium(),
            ]);
        }
    }

    private function checkEvalJusticePerson(?EvalJusticePerson $evalJusticePerson = null): void
    {
        if (null === $evalJusticePerson) {
            $this->maxPoints += 2;

            return;
        }

        $this->isFilled($evalJusticePerson->getJusticeStatus());
        $this->isFilled($evalJusticePerson->getJusticeAct());
    }

    private function reinitProperties(): void
    {
        $this->maxPoints = 0;
        $this->points = 0;
        $this->service = null;
        $this->nbChildren = 0;
    }

    private function checkValues(array $values): void
    {
        foreach ($values as $value) {
            $this->isFilled($value);
        }
    }

    private function isFilled($value, ?array $choices = null): bool
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
