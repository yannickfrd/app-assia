<?php

namespace App\Service\Export;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;

trait EvaluationPersonDataTrait
{
    use SupportPersonDataTrait;

    protected function getEvaluationPersonDatas(SupportPerson $supportPerson): array
    {
        $this->datas = $this->getSupportPersonDatas($supportPerson, $this->anonymized);
        $evaluations = $supportPerson->getEvaluations();
        $evaluationPerson = $evaluations[$evaluations->count() - 1] ?? $this->evaluationPerson;
        /** @var EvaluationGroup $evaluationGroup */
        $evaluationGroup = $evaluationPerson->getEvaluationGroup() ?? $this->evaluationGroup;

        // $evalJusticePerson = $evaluationPerson->getEvalJusticePerson() ?? $this->evalJusticePerson;
        // $evalSocialGroup = $evaluationGroup->getEvalSocialGroup() ?? $this->evalSocialGroup;
        /** @var EvalSocialPerson $evalSocialPerson */
        $evalSocialPerson = $evaluationPerson->getEvalSocialPerson() ?? $this->evalSocialPerson;
        /** @var EvalAdmPerson $evalAdmPerson */
        $evalAdmPerson = $evaluationPerson->getEvalAdmPerson() ?? $this->evalAdmPerson;
        // $evalFamilyGroup = $evaluationGroup->getEvalFamilyGroup() ?? $this->evalFamilyGroup;
        /** @var EvalFamilyPerson $evalFamilyPerson */
        $evalFamilyPerson = $evaluationPerson->getEvalFamilyPerson() ?? $this->evalFamilyPerson;
        /** @var EvalProfPerson $evalProfPerson */
        $evalProfPerson = $evaluationPerson->getEvalProfPerson() ?? $this->evalProfPerson;
        /** @var EvalBudgetGroup $evalBudgetGroup */
        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup() ?? $this->evalBudgetGroup;
        /** @var EvalBudgetPerson $evalBudgetPerson */
        $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson() ?? $this->evalBudgetPerson;
        /** @var EvalHousingGroup $evalHousingGroup */
        $evalHousingGroup = $evaluationGroup->getEvalHousingGroup() ?? $this->evalHousingGroup;

        $this->datas = array_merge($this->datas, [
            'Situation matrimoniale' => $evalFamilyPerson->getMaritalStatusToString(),
            'Enfant ?? na??tre' => $evalFamilyPerson->getUnbornChildToString(),
            'Date terme grossesse' => $this->formatDate($evalFamilyPerson->getExpDateChildbirth()),
            // Admin
            'Nationalit??' => $evalAdmPerson->getNationalityToString(),
            'Papier' => Choices::YES === $evalAdmPerson->getPaper() ?
                $evalAdmPerson->getPaperTypeToString() : $evalAdmPerson->getPaperToString(),
            'Parcours asile' => Choices::YES === $evalAdmPerson->getAsylumBackground() ?
                $evalAdmPerson->getAsylumStatusToString() : $evalAdmPerson->getAsylumBackgroundToString(),
            // Budget
            'Montant total ressources m??nage' => $evalBudgetGroup->getResourcesGroupAmt(),
            'Montant total charges m??nage' => $evalBudgetGroup->getChargesGroupAmt(),
            'Montant total dettes m??nage' => $evalBudgetGroup->getDebtsGroupAmt(),
            'Ressource' => $evalBudgetPerson->getResourceToString(),
            'Montant ressources' => $evalBudgetPerson->getResourcesAmt(),
            'Type de ressources' => $evalBudgetPerson->getEvalBudgetResourcesToString(),
            'Montant charges' => $evalBudgetPerson->getChargesAmt(),
            'Type de charges' => $evalBudgetPerson->getEvalBudgetChargesToString(),
            'Montant dettes' => $evalBudgetPerson->getDebtsAmt(),
            'Type de dettes' => $evalBudgetPerson->getEvalBudgetDebtsToString(),
            // Prof
            'Emploi' => $evalProfPerson->getProfStatusToString(),
            'Type de contrat' => $evalProfPerson->getContractTypeToString(),
            // Social
            'Couverture maladie' => Choices::YES === $evalSocialPerson->getRightSocialSecurity() ?
                $evalSocialPerson->getSocialSecurityToString() : $evalSocialPerson->getRightSocialSecurityToString(),
            'Probl??matique sant??' => $evalSocialPerson->getPhysicalHealthProblemToString().
                (Choices::YES === $evalSocialPerson->getPhysicalHealthProblem() ? ' ('.
                join(', ', $evalSocialPerson->getHealthProblemTypes()).')' : ''),
            'Suivi/parcours ASE' => $evalSocialPerson->getAseFollowUpToString(),
            'PVV' => $evalSocialPerson->getViolenceVictimToString().
                (Choices::YES === $evalSocialPerson->getDomViolenceVictim() ? ' (FVVC)' : ''),
            // Logement
            'Demande SIAO' => $evalHousingGroup->getSiaoRequestToString(),
            'Date demande initiale SIAO' => $this->formatDate($evalHousingGroup->getSiaoRequestDate()),
            'Date derni??re actualisation SIAO' => $this->formatDate($evalHousingGroup->getSiaoUpdatedRequestDate()),
            'Pr??conisation SIAO' => $evalHousingGroup->getSiaoRecommendationToString(),
            'Demande de logement social' => $evalHousingGroup->getSocialHousingRequestToString(),
            'Date DLS' => $this->formatDate($evalHousingGroup->getSocialHousingUpdatedRequestDate()),
            'SYPLO' => $evalHousingGroup->getSyploToString(),
            'Date de labellisation SYPLO' => $this->formatDate($evalHousingGroup->getSyploDate()),
            'Domiciliation' => $evalHousingGroup->getDomiciliationToString().
                ($evalHousingGroup->getDomiciliationZipcode() ? ' ('.$evalHousingGroup->getDomiciliationDept().')' : null),
        ]);

        return $this->datas;
    }
}
