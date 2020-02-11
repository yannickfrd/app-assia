<?php

namespace App\Export;

use App\Entity\SupportPerson;
use App\Entity\EvalSocialGroup;
use App\Entity\EvalAdmPerson;
use App\Entity\EvalFamilyGroup;
use App\Entity\EvalFamilyPerson;
use App\Entity\EvalProfPerson;
use App\Entity\EvalBudgetGroup;
use App\Entity\EvalBudgetPerson;
use App\Entity\EvalHousingGroup;
use App\Entity\EvaluationGroup;
use App\Entity\EvaluationPerson;
use App\Form\Support\Evaluation\EvalSocialGroupType;
use App\Service\Export;
use App\Service\ObjectToArray;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SupportPersonFullExport
{
    protected $arrayData;
    protected $objectToArray;
    protected $datas;

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

        $this->evaluationPerson = new EvaluationPerson();
        $this->evalAdmPerson = new EvalAdmPerson();
        $this->evalBudgetPerson = new EvalBudgetPerson();
        $this->evalFamilyPerson = new EvalFamilyPerson();
        $this->evalProfPerson = new EvalProfPerson();

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

        foreach ($supportPerson->getEvaluationsPerson() as $evaluation) {
            $this->evaluationPerson = $evaluation;
            $this->evaluationGroup = $evaluation->getEvaluationGroup();
        }

        $this->mergeObject($this->evalSocialGroup, $this->evaluationGroup->getEvalSocialGroup(), "evalSocialGroup", "social");
        $this->mergeObject($this->evalAdmPerson, $this->evaluationPerson->getEvalAdmPerson(), "evalAdmPerson", "adm");
        $this->mergeObject($this->evalFamilyGroup, $this->evaluationGroup->getEvalFamilyGroup(), "evalFamilyGroup", "family");
        $this->mergeObject($this->evalFamilyPerson, $this->evaluationPerson->getEvalFamilyPerson(), "evalFamilyPerson", "family");
        $this->mergeObject($this->evalProfPerson, $this->evaluationPerson->getEvalProfPerson(), "evalProfPerson", "prof");
        $this->mergeObject($this->evalBudgetGroup, $this->evaluationGroup->getEvalBudgetGroup(), "evalBudgetGroup", "budget");
        $this->mergeObject($this->evalBudgetPerson, $this->evaluationPerson->getEvalBudgetPerson(), "evalBudgetPerson", "budget");
        $this->mergeObject($this->evalHousingGroup, $this->evaluationGroup->getEvalHousingGroup(), "evalHousingGroup", "housing");

        return $this->datas;
    }

    protected function mergeObject($nameObject, $emtpyObject, $object, $translation)
    {
        $array = $this->objectToArray->getArray($nameObject, $emtpyObject, $object, $translation);
        $this->datas = array_merge($this->datas, $array);
    }
}
