<?php

namespace App\Service\Indicators;

use App\Entity\EvalBudgetPerson;
use App\Entity\EvalProfPerson;
use App\Entity\EvaluationPerson;
use App\Entity\PeopleGroup;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\SupportPerson;
use App\Form\Utils\Choices;

class SocialIndicators
{
    protected $varDatas;
    protected $nbGroups = 0;
    protected $typologyDatas;
    protected $genderDatas;
    protected $roleDatas;

    protected $profStatusDatas;

    public function __construct()
    {
    }

    public function getResults($supportPeople)
    {
        $datas = [];

        $this->typologyDatas = $this->initVar(PeopleGroup::FAMILY_TYPOLOGY);
        $this->genderDatas = $this->initVar(Person::GENDER);
        $this->roleDatas = $this->initVar(RolePerson::ROLE);
        $this->profStatusDatas = $this->initVar(EvalProfPerson::PROF_STATUS);
        $this->contractTypeDatas = $this->initVar(EvalProfPerson::CONTRACT_TYPE);
        $this->resourcesDatas = $this->initVar(Choices::YES_NO_IN_PROGRESS);
        $this->chargesDatas = $this->initVar(Choices::YES_NO_IN_PROGRESS);

        foreach ($supportPeople as $supportPerson) {
            /** @var SupportPerson */
            $supportPerson = $supportPerson;

            $this->typologyDatas = $this->updateVar(
                $supportPerson,
                $supportPerson->getSupportGroup()->getPeopleGroup()->getFamilyTypology(),
                $this->typologyDatas
            );
            $this->genderDatas = $this->updateVar(
                $supportPerson,
                $supportPerson->getPerson()->getGender(),
                $this->genderDatas,
            );
            $this->roleDatas = $this->updateVar(
                $supportPerson,
                $supportPerson->getRole(),
                $this->roleDatas,
            );

            $evaluations = $supportPerson->getEvaluationsPerson();
            /** @var EvaluationPerson */
            $evaluationPerson = $evaluations ? $evaluations[($evaluations->count()) - 1] : new EvaluationPerson();

            $evalProfPerson = $evaluationPerson && $evaluationPerson->getEvalProfPerson() ? $evaluationPerson->getEvalProfPerson() : new EvalProfPerson();
            $evalBudgetPerson = $evaluationPerson && $evaluationPerson->getEvalBudgetPerson() ? $evaluationPerson->getEvalBudgetPerson() : new EvalBudgetPerson();

            $this->profStatusDatas = $this->updateVar($supportPerson, $evalProfPerson->getProfStatus(), $this->profStatusDatas);
            $this->contractTypeDatas = $this->updateVar($supportPerson, $evalProfPerson->getContractType(), $this->contractTypeDatas);

            $this->resourcesDatas = $this->updateVar($supportPerson, $evalBudgetPerson->getResources(), $this->resourcesDatas);
            $this->chargesDatas = $this->updateVar($supportPerson, $evalBudgetPerson->getCharges(), $this->chargesDatas);

            if ($supportPerson->getHead()) {
                ++$this->nbGroups;
            }
        }
        $datas['Typologie familiale'] = $this->typologyDatas;
        $datas['Sexe'] = $this->genderDatas;
        $datas['Rôle'] = $this->roleDatas;
        $datas['Statut professionnel'] = $this->profStatusDatas;
        $datas['Type de contrat de travail'] = $this->contractTypeDatas;
        $datas['Ressources'] = $this->resourcesDatas;
        $datas['Charges'] = $this->chargesDatas;

        $datas['nbGroups'] = $this->nbGroups;
        $datas['nbPeople'] = count($supportPeople);

        // dump($datas);

        return $datas;
    }

    protected function initVar(array $values)
    {
        $array = [];
        foreach ($values as $key => $value) {
            $array[$key] = [
                'name' => $value,
                'nbGroups' => 0,
                'nbPeople' => 0,
            ];
        }

        return $array;
    }

    protected function updateVar(SupportPerson $supportPerson, int $var = null, array $varDatas)
    {
        if (null === $var) {
            $var = 99;
        }
        try {
            $varDatas[$var]['nbPeople'] = $varDatas[$var]['nbPeople'] + 1;

            if ($supportPerson->getHead()) {
                $varDatas[$var]['nbGroups'] = $varDatas[$var]['nbGroups'] + 1;
            }
        } catch (\Throwable $th) {
        }

        return $varDatas;
    }
}
