<?php

namespace App\Service\Export;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalFamilyGroup;
use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalJusticePerson;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialGroup;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\InitEvalGroup;
use App\Entity\Evaluation\InitEvalPerson;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\Choices;
use App\Service\ExportExcel;
use App\Service\Normalisation;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class EvaluationSupportPersonExport extends ExportExcel
{
    protected $normalisation;
    protected $logger;
    protected $stopwatch;
    protected $datas;

    protected $initEvalGroup;
    protected $initEvalPerson;

    protected $evaluationPerson;
    protected $evalJusticePerson;
    protected $evalSocialPerson;
    protected $evalAdmPerson;
    protected $evalBudgetPerson;
    protected $evalFamilyPerson;
    protected $evalProfPerson;

    protected $evaluationGroup;
    protected $evalSocialGroup;
    protected $evalFamilyGroup;
    protected $evalBudgetGroup;
    protected $evalHousingGroup;

    public function __construct(Normalisation $normalisation, LoggerInterface $logger, Stopwatch $stopwatch)
    {
        $this->normalisation = $normalisation;
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;

        $this->initEvalGroup = new InitEvalGroup();
        $this->initEvalPerson = new InitEvalPerson();

        $this->evaluationGroup = new EvaluationGroup();
        $this->evalBudgetGroup = new EvalBudgetGroup();
        $this->evalFamilyGroup = new EvalFamilyGroup();
        $this->evalHousingGroup = new EvalHousingGroup();
        $this->evalSocialGroup = new EvalSocialGroup();

        $this->evaluationPerson = new EvaluationPerson();
        $this->evalJusticePerson = new EvalJusticePerson();
        $this->evalAdmPerson = new EvalAdmPerson();
        $this->evalBudgetPerson = new EvalBudgetPerson();
        $this->evalFamilyPerson = new EvalFamilyPerson();
        $this->evalProfPerson = new EvalProfPerson();
        $this->evalSocialPerson = new EvalSocialPerson();
    }

    /**
     * Exporte les données.
     */
    public function exportData($supports)
    {
        $this->stopwatch->start('create_datas_array');

        $arrayData = [];
        $arrayData[] = $this->normalisation->getKeys(array_keys($this->getDatas($supports[0])), ['evaluation']);

        $i = 0;
        $nbSupports = count($supports);
        foreach ($supports as $supportPerson) {
            $arrayData[] = $this->getDatas($supportPerson);
            if ($i > 100) {
                // sleep(5);
                $this->logger->info(count($arrayData).' / '.$nbSupports);
                $i = 1;
            }
            ++$i;
        }

        $this->stopwatch->stop('create_datas_array');
        $this->logger->info($this->stopwatch->getEvent('create_datas_array'));

        $this->stopwatch->start('create_sheet');

        $this->createSheet('export_suivis', 'xlsx', $arrayData, 15);

        $this->stopwatch->stop('create_sheet');
        $this->logger->info($this->stopwatch->getEvent('create_sheet'));

        return $this->exportFile(true);
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(SupportPerson $supportPerson): array
    {
        $this->datas = (new SupportPersonExport())->getDatas($supportPerson);
        $evaluations = $supportPerson->getEvaluationsPerson();
        $evaluationPerson = $evaluations[$evaluations->count() - 1] ?? $this->evaluationPerson;
        $evaluationGroup = $evaluationPerson->getEvaluationGroup() ?? $this->evaluationGroup;

        $initEvalGroup = $evaluationGroup->getInitEvalGroup() ?? $this->initEvalGroup;
        $initEvalPerson = $evaluationPerson->getInitEvalPerson() ?? $this->initEvalPerson;
        $evalJusticePerson = $evaluationPerson->getEvalJusticePerson() ?? $this->evalJusticePerson;
        $evalSocialGroup = $evaluationGroup->getEvalSocialGroup() ?? $this->evalSocialGroup;
        $evalSocialPerson = $evaluationPerson->getEvalSocialPerson() ?? $this->evalSocialPerson;
        $evalAdmPerson = $evaluationPerson->getEvalAdmPerson() ?? $this->evalAdmPerson;
        $evalFamilyGroup = $evaluationGroup->getEvalFamilyGroup() ?? $this->evalFamilyGroup;
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

            //
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
            'Ressources' => $evalBudgetPerson->getResourcesToString(),
            // 'Montant ressources' => $evalBudgetPerson->getResourcesAmt(),
            // 'Montant salaire' => $evalBudgetPerson->getSalaryAmt(),
            // 'Montant ARE' => $evalBudgetPerson->getUnemplBenefitAmt(),
            // 'Montant RSA' => $evalBudgetPerson->getMinimumIncomeAmt(),
            // 'Montant dettes' => $evalBudgetPerson->getDebtsAmt(),
            'Montant total ressources ménage' => $evalBudgetGroup->getResourcesGroupAmt(),
            'Type ressources' => join(', ', $evalBudgetPerson->getResourcesType()),
            'Montant total charges ménage' => $evalBudgetGroup->getChargesGroupAmt(),
            'Montant total dettes ménage' => $evalBudgetGroup->getDebtsGroupAmt(),
            'Type dettes' => join(', ', $evalBudgetPerson->getDebtsType()),
            // 'Montant participation financière' => $evalBudgetGroup->getContributionAmt(),

            // Prof
            'Emploi' => $evalProfPerson->getProfStatusToString(),
            'Type de contrat' => $evalProfPerson->getContractTypeToString(),

            // Social
            'Couverture maladie' => Choices::YES === $evalSocialPerson->getRightSocialSecurity() ?
                $evalSocialPerson->getSocialSecurityToString() : $evalSocialPerson->getRightSocialSecurityToString(),
            'Problématique santé physique' => $evalSocialPerson->getPhysicalHealthProblemToString(),
            'Problématique santé mentale' => $evalSocialPerson->getMentalHealthProblemToString(),
            'Problématique d\'ddiction' => $evalSocialPerson->getAddictionProblemToString(),
            'Suivi/parcours ASE' => $evalSocialPerson->getAseFollowUpToString(),
            // 'Service soin ou acc. à domicile' => Choices::YES === $evalSocialPerson->getHomeCareSupport() ?
            //    $evalSocialPerson->getHomeCareSupportTypeToString() : $evalSocialPerson->getHomeCareSupportToString(),
            // 'Mesure de protection' => $evalFamilyPerson->getProtectiveMeasureToString(),
            'PVV' => $evalSocialPerson->getViolenceVictimToString().
                (Choices::YES === $evalSocialPerson->getDomViolenceVictim() ? ' (FVVC)' : ''),

            // Logement
            'Demande de logement social' => $evalHousingGroup->getSocialHousingRequestToString(),
            'Date DLS' => $this->formatDate($evalHousingGroup->getSocialHousingRequestDate()),
            'Demande SIAO' => $evalHousingGroup->getSiaoRequestToString(),
            'Date demande initiale SIAO' => $this->formatDate($evalHousingGroup->getSiaoRequestDate()),
            'Date dernière actualisation SIAO' => $this->formatDate($evalHousingGroup->getSiaoUpdatedRequestDate()),
            'Préconisation SIAO' => $evalHousingGroup->getSiaoRecommendationToString(),
        ]);

        return $this->datas;
    }
}
