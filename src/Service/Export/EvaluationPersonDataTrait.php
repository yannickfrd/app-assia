<?php

namespace App\Service\Export;

use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;

trait EvaluationPersonDataTrait
{
    use SupportPersonDataTrait;

    protected function getEvaluationPersonDatas(SupportPerson $supportPerson): array
    {
        $this->datas = $this->getSupportPersonDatas($supportPerson, $this->anonymized);
        $evaluations = $supportPerson->getEvaluationsPerson();
        $evaluationPerson = $evaluations[$evaluations->count() - 1] ?? $this->evaluationPerson;
        $evaluationGroup = $evaluationPerson->getEvaluationGroup() ?? $this->evaluationGroup;

        // $initEvalGroup = $evaluationGroup->getInitEvalGroup() ?? $this->initEvalGroup;
        // $initEvalPerson = $evaluationPerson->getInitEvalPerson() ?? $this->initEvalPerson;
        // $evalJusticePerson = $evaluationPerson->getEvalJusticePerson() ?? $this->evalJusticePerson;
        // $evalSocialGroup = $evaluationGroup->getEvalSocialGroup() ?? $this->evalSocialGroup;
        $evalSocialPerson = $evaluationPerson->getEvalSocialPerson() ?? $this->evalSocialPerson;
        $evalAdmPerson = $evaluationPerson->getEvalAdmPerson() ?? $this->evalAdmPerson;
        // $evalFamilyGroup = $evaluationGroup->getEvalFamilyGroup() ?? $this->evalFamilyGroup;
        $evalFamilyPerson = $evaluationPerson->getEvalFamilyPerson() ?? $this->evalFamilyPerson;
        $evalProfPerson = $evaluationPerson->getEvalProfPerson() ?? $this->evalProfPerson;
        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup() ?? $this->evalBudgetGroup;
        $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson() ?? $this->evalBudgetPerson;
        $evalHousingGroup = $evaluationGroup->getEvalHousingGroup() ?? $this->evalHousingGroup;

        $this->datas = array_merge($this->datas, [
            'Situation matrimoniale' => $evalFamilyPerson->getMaritalStatusToString(),
            'Enfant à naître' => $evalFamilyPerson->getUnbornChildToString(),
            'Date terme grossesse' => $this->formatDate($evalFamilyPerson->getExpDateChildbirth()),
            // 'Situation résidentielle (avant entrée)' => $initEvalGroup->getHousingStatusToString(),
            // 'Raison principale de la demande' => $evalSocialGroup->getReasonRequestToString(),
            // 'Durée d\'errance' => $evalSocialGroup->getWanderingTimeToString(),
            // 'Rupture liens familiaux' => $evalSocialPerson->getFamilyBreakdownToString(),
            // 'Rupture liens amicaux' => $evalSocialPerson->getFriendshipBreakdownToString(),

            // init
            // 'Papier (entrée)' => Choices::YES === $initEvalPerson->getPaper() ?
            //     $initEvalPerson->getPaperTypeToString() : $initEvalPerson->getPaperToString(),
            // 'Ressources (entrée)' => $initEvalPerson->getResourcesToString(),
            // 'Type ressources (entrée)' => join(', ', $this->getResources($initEvalPerson)),
            // 'Montant ressources (entrée)' => $initEvalPerson->getResourcesAmt(),
            // // 'Montant salaire (entrée)' => $initEvalPerson->getSalaryAmt(),
            // // 'Montant ARE (entrée)' => $initEvalPerson->getUnemplBenefitAmt(),
            // // 'Montant RSA (entrée)' => $initEvalPerson->getMinimumIncomeAmt(),
            // 'Emploi (entrée)' => $initEvalPerson->getProfStatusToString(),
            // 'Montant total ressources ménage (entrée)' => $initEvalGroup->getResourcesGroupAmt(),
            // 'Couverture maladie (entrée)' => Choices::YES === $initEvalPerson->getRightSocialSecurity() ?
            //     $initEvalPerson->getSocialSecurityToString() : $initEvalPerson->getRightSocialSecurityToString(),
            // 'Demande de logement social (entrée)' => $initEvalGroup->getSocialHousingRequestToString(),
            // 'Demande SIAO (entrée)' => $initEvalGroup->getSiaoRequestToString(),

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
            'Ressources' => $evalBudgetPerson->getResourcesToString(),
            'Montant ressources' => $evalBudgetPerson->getResourcesAmt(),
            // 'Montant salaire' => $evalBudgetPerson->getSalaryAmt(),
            // 'Montant ARE' => $evalBudgetPerson->getUnemplBenefitAmt(),
            // 'Montant RSA' => $evalBudgetPerson->getMinimumIncomeAmt(),
            'Type de ressources' => join(', ', $evalBudgetPerson->getResourceTypes()),
            'Montant charges' => $evalBudgetPerson->getChargesAmt(),
            'Type de charges' => join(', ', $evalBudgetPerson->getChargeTypes()),
            'Montant dettes' => $evalBudgetPerson->getDebtsAmt(),
            'Type de dettes' => join(', ', $evalBudgetPerson->getDebtTypes()),

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
