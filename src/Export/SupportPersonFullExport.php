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
use App\Entity\EvalSocialPerson;
use App\Entity\EvaluationPerson;

use App\Export\SupportPersonExport;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SupportPersonFullExport
{
    protected $normalizer;
    protected $translator;
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

    public function __construct(NormalizerInterface $normalizer, TranslatorInterface $translator)
    {
        $this->normalizer = $normalizer;
        $this->translator = $translator;

        $this->initEvalGroup = new InitEvalGroup();
        $this->initEvalPerson = new InitEvalPerson();

        $this->evaluationGroup = new EvaluationGroup();
        $this->evalBudgetGroup = new EvalBudgetGroup();
        $this->evalFamilyGroup = new EvalFamilyGroup();
        $this->evalHousingGroup = new EvalHousingGroup();
        $this->evalSocialGroup = new EvalSocialGroup();

        $this->evaluationPerson = new EvaluationPerson();
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
        $arrayData[] =  $this->getKeys(array_keys($this->getDatas($supports[0])));

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
        $this->evaluationPerson = $evaluations[count($evaluations) - 1] ?? $this->evaluationPerson;
        $this->evaluationGroup = $this->evaluationPerson->getEvaluationGroup() ?? $this->evaluationGroup;

        $this->normalize($supportPerson->getSupportGroup()->getInitEvalGroup() ?? $this->initEvalGroup, "initEval");
        $this->normalize($supportPerson->getInitEvalPerson() ?? $this->initEvalPerson, "initEval");
        $this->normalize($this->evaluationGroup->getEvalSocialGroup() ?? $this->evalSocialGroup, "social");
        $this->normalize($this->evaluationPerson->getEvalSocialPerson() ?? $this->evalSocialPerson, "social");
        $this->normalize($this->evaluationPerson->getEvalAdmPerson() ?? $this->evalAdmPerson, "adm");
        $this->normalize($this->evaluationGroup->getEvalFamilyGroup() ?? $this->evalFamilyGroup, "family");
        $this->normalize($this->evaluationPerson->getEvalFamilyPerson() ?? $this->evalFamilyPerson, "family");
        $this->normalize($this->evaluationPerson->getEvalProfPerson() ?? $this->evalProfPerson, "prof");
        $this->normalize($this->evaluationGroup->getEvalBudgetGroup() ?? $this->evalBudgetGroup, "budget");
        $this->normalize($this->evaluationPerson->getEvalBudgetPerson() ?? $this->evalBudgetPerson, "budget");
        $this->normalize($this->evaluationGroup->getEvalHousingGroup() ?? $this->evalHousingGroup, "housing");

        return $this->datas;
    }

    // Normalise l'entité
    protected function normalize($entity, $translation = null)
    {
        $array = $this->normalizer->normalize($entity, null, ["groups" => "export"]);

        foreach ($array as $key => $value) {
            if ($value && stristr($key, "Date")) {
                $array[$key] =  Date::stringToExcel(substr($value, 0, 10));
            }
            $newKey = $key . "#" . $translation;
            $array[$newKey] = $array[$key];
            unset($array[$key]);
        }
        $this->datas = array_merge($this->datas, $array);
    }

    // Inverse l'écriture en camelCase
    protected function unCamelCase($content, $separator = " ", $translation)
    {
        $content = preg_replace("#(?<=[a-zA-Z])([A-Z])(?=[a-zA-Z])#", $separator . "$1", $content);
        return $this->translator->trans(ucfirst(strtolower($content)), [], $translation);
    }

    protected function getKeys($array)
    {
        $arrayKeys = [];
        foreach ($array as $value) {
            if (stristr($value, "#")) {
                $array = explode("#", $value, 2);
                $translation = $array[1];
                $value = $this->unCamelCase(str_replace("ToString", "", $array[0]), " ", $translation);
                $translation = $this->unCamelCase($translation, " ", $translation);
                $arrayKeys[] = $value . " [" . $translation . "]";
            } else {
                $arrayKeys[] = $value;
            }
        }
        return $arrayKeys;
    }
}
