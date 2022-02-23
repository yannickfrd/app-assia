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

        // $evalInitGroup = $evaluationGroup->getEvalInitGroup() ?? $this->evalInitGroup;
        // $evalInitPerson = $evaluationPerson->getEvalInitPerson() ?? $this->evalInitPerson;
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
            'Enfant à naître' => $evalFamilyPerson->getUnbornChildToString(),
            'Date terme grossesse' => $this->formatDate($evalFamilyPerson->getExpDateChildbirth()),
            // 'Situation résidentielle (avant entrée)' => $evalInitGroup->getHousingStatusToString(),
            // 'Raison principale de la demande' => $evalSocialGroup->getReasonRequestToString(),
            // 'Durée d\'errance' => $evalSocialGroup->getWanderingTimeToString(),
            // 'Rupture liens familiaux' => $evalSocialPerson->getFamilyBreakdownToString(),
            // 'Rupture liens amicaux' => $evalSocialPerson->getFriendshipBreakdownToString(),

            // init
            // 'Papier (entrée)' => Choices::YES === $evalInitPerson->getPaper() ?
            //  $evalInitPerson->getPaperTypeToString() : $evalInitPerson->getPaperToString(),
            // 'Ressource (entrée)' => $evalInitPerson->getResourceToString(),
            // 'Type ressources (entrée)' => join(', ', $this->getEvalBudgetResourcesToString($evalInitPerson)),
            // 'Montant ressources (entrée)' => $evalInitPerson->getResourcesAmt(),
            // // 'Montant salaire (entrée)' => $evalInitPerson->getSalaryAmt(),
            // // 'Montant ARE (entrée)' => $evalInitPerson->getUnemplBenefitAmt(),
            // // 'Montant RSA (entrée)' => $evalInitPerson->getMinimumIncomeAmt(),
            // 'Emploi (entrée)' => $evalInitPerson->getProfStatusToString(),
            // 'Montant total ressources ménage (entrée)' => $evalInitGroup->getResourcesGroupAmt(),
            // 'Couverture maladie (entrée)' => Choices::YES === $evalInitPerson->getRightSocialSecurity() ?
            //     $evalInitPerson->getSocialSecurityToString() : $evalInitPerson->getRightSocialSecurityToString(),
            // 'Demande de logement social (entrée)' => $evalInitGroup->getSocialHousingRequestToString(),
            // 'Demande SIAO (entrée)' => $evalInitGroup->getSiaoRequestToString(),

            // Admin
            'Nationalité' => $evalAdmPerson->getNationalityToString(),
            'Papier' => Choices::YES === $evalAdmPerson->getPaper() ?
                $evalAdmPerson->getPaperTypeToString() : $evalAdmPerson->getPaperToString(),
            'Parcours asile' => Choices::YES === $evalAdmPerson->getAsylumBackground() ?
                $evalAdmPerson->getAsylumStatusToString() : $evalAdmPerson->getAsylumBackgroundToString(),

            // Budget
            'Montant total ressources ménage' => $evalBudgetGroup->getResourcesGroupAmt(),
            'Montant total charges ménage' => $evalBudgetGroup->getChargesGroupAmt(),
            'Montant total dettes ménage' => $evalBudgetGroup->getDebtsGroupAmt(),
            'Ressource' => $evalBudgetPerson->getResourceToString(),
            'Montant ressources' => $evalBudgetPerson->getResourcesAmt(),
            // 'Montant salaire' => $evalBudgetPerson->getSalaryAmt(),
            // 'Montant ARE' => $evalBudgetPerson->getUnemplBenefitAmt(),
            // 'Montant RSA' => $evalBudgetPerson->getMinimumIncomeAmt(),
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
            'Problématique santé' => $evalSocialPerson->getPhysicalHealthProblemToString().
                (Choices::YES === $evalSocialPerson->getPhysicalHealthProblem() ? ' ('.
                join(', ', $evalSocialPerson->getHealthProblemTypes()).')' : ''),
            'Suivi/parcours ASE' => $evalSocialPerson->getAseFollowUpToString(),
            // 'Service soin ou acc. à domicile' => Choices::YES === $evalSocialPerson->getHomeCareSupport() ?
            //    $evalSocialPerson->getHomeCareSupportTypeToString() : $evalSocialPerson->getHomeCareSupportToString(),
            // 'Mesure de protection' => $evalFamilyPerson->getProtectiveMeasureToString(),
            'PVV' => $evalSocialPerson->getViolenceVictimToString().
                (Choices::YES === $evalSocialPerson->getDomViolenceVictim() ? ' (FVVC)' : ''),

            // Logement
            'Demande de logement social' => $evalHousingGroup->getSocialHousingRequestToString(),
            'Date DLS' => $this->formatDate($evalHousingGroup->getSocialHousingRequestDate()),
            'SYPLO' => $evalHousingGroup->getSyploToString(),
            'Date de labellisation SYPLO' => $this->formatDate($evalHousingGroup->getSyploDate()),
            'Demande SIAO' => $evalHousingGroup->getSiaoRequestToString(),
            'Date demande initiale SIAO' => $this->formatDate($evalHousingGroup->getSiaoRequestDate()),
            'Date dernière actualisation SIAO' => $this->formatDate($evalHousingGroup->getSiaoUpdatedRequestDate()),
            'Préconisation SIAO' => $evalHousingGroup->getSiaoRecommendationToString(),
            'Domiciliation' => $evalHousingGroup->getDomiciliationToString().
                ($evalHousingGroup->getDomiciliationZipcode() ? ' ('.$evalHousingGroup->getDomiciliationDept().')' : null),
        ]);

        return $this->datas;
    }
}
