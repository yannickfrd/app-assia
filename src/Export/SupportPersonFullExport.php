<?php

namespace App\Export;

use App\Service\Export;
use App\Entity\EvalAdmPerson;
use App\Entity\InitEvalGroup;
use App\Entity\SupportPerson;
use App\Entity\EvalProfPerson;
use App\Entity\InitEvalPerson;
use App\Entity\EvalBudgetGroup;
use App\Entity\EvalFamilyGroup;
use App\Entity\EvalSocialGroup;
use App\Entity\EvaluationGroup;
use App\Entity\EvalBudgetPerson;
use App\Entity\EvalFamilyPerson;
use App\Entity\EvalHousingGroup;
use App\Entity\EvalJusticePerson;
use App\Entity\EvalSocialPerson;
use App\Entity\EvaluationPerson;

use App\Export\SupportPersonExport;

use App\Service\Normalisation;

class SupportPersonFullExport
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
     * Exporte les données
     */
    public function exportData($supports)
    {
        $arrayData = [];
        $arrayData[] =  $this->normalisation->getKeys(array_keys($this->getDatas($supports[0])));

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

        $this->add($this->evaluationPerson->getEvalJusticePerson() ?? $this->evalJusticePerson, "justice");
        $this->add($this->evaluationGroup->getInitEvalGroup() ?? $this->initEvalGroup, "initEval");
        $this->add($this->evaluationPerson->getInitEvalPerson() ?? $this->initEvalPerson, "initEval");
        $this->add($this->evaluationGroup->getEvalSocialGroup() ?? $this->evalSocialGroup, "social");
        $this->add($this->evaluationPerson->getEvalSocialPerson() ?? $this->evalSocialPerson, "social");
        $this->add($this->evaluationPerson->getEvalAdmPerson() ?? $this->evalAdmPerson, "adm");
        $this->add($this->evaluationGroup->getEvalFamilyGroup() ?? $this->evalFamilyGroup, "family");
        $this->add($this->evaluationPerson->getEvalFamilyPerson() ?? $this->evalFamilyPerson, "family");
        $this->add($this->evaluationPerson->getEvalProfPerson() ?? $this->evalProfPerson, "prof");
        $this->add($this->evaluationGroup->getEvalBudgetGroup() ?? $this->evalBudgetGroup, "budget");
        $this->add($this->evaluationPerson->getEvalBudgetPerson() ?? $this->evalBudgetPerson, "budget");
        $this->add($this->evaluationGroup->getEvalHousingGroup() ?? $this->evalHousingGroup, "housing");

        return $this->datas;
    }

    /**
     * Ajoute l'objet normalisé
     *
     * @param Object $object
     * @param string $name
     */
    protected function add(Object $object, string $name = null)
    {
        $this->datas = array_merge($this->datas, $this->normalisation->normalize($object, $name));
    }
}
