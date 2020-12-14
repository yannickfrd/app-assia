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
use App\Service\ExportExcel;
use App\Service\Normalisation;

class SupportPersonFullExport extends ExportExcel
{
    protected $normalisation;
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

    public function __construct(Normalisation $normalisation)
    {
        $this->normalisation = $normalisation;

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
        $arrayData = [];
        $arrayData[] = $this->normalisation->getKeys(array_keys($this->getDatas($supports[0])), 'evaluation');

        $i = 0;
        foreach ($supports as $supportPerson) {
            $arrayData[] = $this->getDatas($supportPerson);
            if ($i > 100) {
                sleep(5);
                $i = 1;
            }
            ++$i;
        }

        $this->createSheet('export_suivis', 'xlsx', $arrayData, 15);

        return $this->exportFile(true);
    }

    /**
     * Retourne les résultats sous forme de tableau.
     */
    protected function getDatas(SupportPerson $supportPerson): array
    {
        $this->datas = (new SupportPersonExport())->getDatas($supportPerson);

        $evaluations = $supportPerson->getEvaluationsPerson();
        $this->evaluationPerson = $evaluations[$evaluations->count() - 1] ?? new EvaluationPerson();
        $this->evaluationGroup = $this->evaluationPerson->getEvaluationGroup() ?? new EvaluationGroup();

        $this->datas = array_merge($this->datas, [
                'ID évaluation groupe' => $this->evaluationGroup->getId(),
                'ID évaluation personne' => $this->evaluationPerson->getId(),
            ]);

        $this->add($this->evaluationGroup->getInitEvalGroup() ?? $this->initEvalGroup, 'initEval');
        $this->add($this->evaluationPerson->getInitEvalPerson() ?? $this->initEvalPerson, 'initEval');
        $this->add($this->evaluationPerson->getEvalJusticePerson() ?? $this->evalJusticePerson, 'justice');
        $this->add($this->evaluationGroup->getEvalSocialGroup() ?? $this->evalSocialGroup, 'social');
        $this->add($this->evaluationPerson->getEvalSocialPerson() ?? $this->evalSocialPerson, 'social');
        $this->add($this->evaluationPerson->getEvalAdmPerson() ?? $this->evalAdmPerson, 'adm');
        $this->add($this->evaluationGroup->getEvalFamilyGroup() ?? $this->evalFamilyGroup, 'family');
        $this->add($this->evaluationPerson->getEvalFamilyPerson() ?? $this->evalFamilyPerson, 'family');
        $this->add($this->evaluationPerson->getEvalProfPerson() ?? $this->evalProfPerson, 'prof');
        $this->add($this->evaluationGroup->getEvalBudgetGroup() ?? $this->evalBudgetGroup, 'budget');
        $this->add($this->evaluationPerson->getEvalBudgetPerson() ?? $this->evalBudgetPerson, 'budget');
        $this->add($this->evaluationGroup->getEvalHousingGroup() ?? $this->evalHousingGroup, 'housing');

        return $this->datas;
    }
}
