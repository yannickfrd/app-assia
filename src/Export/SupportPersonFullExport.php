<?php

namespace App\Export;

use App\Service\Export;
use App\Entity\EvalAdmPerson;
use App\Entity\SupportPerson;
use App\Entity\EvalProfPerson;
use App\Service\ObjectToArray;
use App\Entity\EvalBudgetGroup;
use App\Entity\EvalFamilyGroup;
use App\Entity\EvalSocialGroup;
use App\Entity\EvaluationGroup;
use App\Entity\EvalBudgetPerson;
use App\Entity\EvalFamilyPerson;
use App\Entity\EvalHousingGroup;
use App\Entity\EvalSocialPerson;
use App\Entity\EvaluationPerson;
use App\Entity\InitEvalGroup;
use App\Entity\InitEvalPerson;

class SupportPersonFullExport
{
    protected $arrayData;
    protected $objectToArray;
    protected $datas;

    protected $initEvalGroup;
    protected $initEvalPerson;

    protected $evaluationPerson;
    protected $evalAdmPerson;
    protected $evalBudgetPerson;
    protected $evalFamilyPerson;
    protected $evalProfPerson;

    protected $evaluationGroup;
    protected $evalSocialGroup;
    protected $evalFamilyGroup;
    protected $evalBudgetGroup;
    protected $evalHousingGroup;

    public function __construct(ObjectToArray $objectToArray)
    {
        $this->arrayData = [];
        $this->objectToArray = $objectToArray;

        $this->initEvalGroup = new InitEvalGroup();
        $this->initEvalPerson = new InitEvalPerson();

        $this->evaluationPerson = new EvaluationPerson();
        $this->evalAdmPerson = new EvalAdmPerson();
        $this->evalBudgetPerson = new EvalBudgetPerson();
        $this->evalFamilyPerson = new EvalFamilyPerson();
        $this->evalProfPerson = new EvalProfPerson();
        $this->evalSocialPerson = new EvalSocialPerson();

        $this->evaluationGroup = new EvaluationGroup();
        $this->evalBudgetGroup = new EvalBudgetGroup();
        $this->evalFamilyGroup = new EvalFamilyGroup();
        $this->evalHousingGroup = new EvalHousingGroup();
        $this->evalSocialGroup = new EvalSocialGroup();
    }

    /**
     * Exporte les données
     */
    public function exportData($supports)
    {
        $arrayData[] = array_keys($this->getDatas($supports[0]));

        foreach ($supports as $supportPerson) {
            $arrayData[] = $this->getDatas($supportPerson);
        }

        $export = new Export("export_suivis", "xlsx", $arrayData, 12.5);

        return $export->exportFile();
    }

    /**
     * Retourne les résultats sous forme de tableau
     * @param SupportPerson $supportPerson
     * @return array
     */
    protected function getDatas(SupportPerson $supportPerson)
    {
        $supportPersonExport = new SupportPersonExport;
        $this->datas = $supportPersonExport->getDatas($supportPerson);

        $evaluations = $supportPerson->getEvaluationsPerson();
        $this->evaluationPerson = $evaluations[count($evaluations) - 1] ?? new EvaluationPerson();
        $this->evaluationGroup = $this->evaluationPerson->getEvaluationGroup() ?? new EvaluationGroup();

        $this->mergeObject($supportPerson->getSupportGroup()->getInitEvalGroup() ?? $this->initEvalGroup, "initEvalGroup", "initEval");
        $this->mergeObject($supportPerson->getInitEvalPerson() ?? $this->initEvalPerson, "initEvalPerson", "initEval");
        $this->mergeObject($this->evaluationGroup->getEvalSocialGroup() ?? $this->evalSocialGroup, "evalSocialGroup", "social");
        $this->mergeObject($this->evaluationPerson->getEvalSocialPerson() ?? $this->evalSocialPerson, "evalSocialPerson", "social");
        $this->mergeObject($this->evaluationPerson->getEvalAdmPerson() ?? $this->evalAdmPerson, "evalAdmPerson", "adm");
        $this->mergeObject($this->evaluationGroup->getEvalFamilyGroup() ?? $this->evalFamilyGroup, "evalFamilyGroup", "family");
        $this->mergeObject($this->evaluationPerson->getEvalFamilyPerson() ?? $this->evalFamilyPerson, "evalFamilyPerson", "family");
        $this->mergeObject($this->evaluationPerson->getEvalProfPerson() ?? $this->evalProfPerson, "evalProfPerson", "prof");
        $this->mergeObject($this->evaluationGroup->getEvalBudgetGroup() ?? $this->evalBudgetGroup, "evalBudgetGroup", "budget");
        $this->mergeObject($this->evaluationPerson->getEvalBudgetPerson() ?? $this->evalBudgetPerson, "evalBudgetPerson", "budget");
        $this->mergeObject($this->evaluationGroup->getEvalHousingGroup() ?? $this->evalHousingGroup, "evalHousingGroup", "housing");

        return $this->datas;
    }

    protected function mergeObject($entity, $entityName, $translation)
    {
        $array = $this->objectToArray->getArray($entity,  $entityName, $translation);
        $this->datas = array_merge($this->datas, $array);
    }
}
