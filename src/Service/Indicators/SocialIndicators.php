<?php

namespace App\Service\Indicators;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportPerson;
use App\Form\Utils\EvaluationChoices;
use Doctrine\Common\Collections\Collection;

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

    /**
     * @param SupportPerson[] $supportPeople
     */
    public function getResults(array $supportPeople): array
    {
        $datas = [];

        $this->typologyDatas = $this->initVar(PeopleGroup::FAMILY_TYPOLOGY);
        $this->genderDatas = $this->initVar(Person::GENDERS);
        $this->roleDatas = $this->initVar(RolePerson::ROLE);
        $this->profStatusDatas = $this->initVar(EvalProfPerson::PROF_STATUS);
        $this->contractTypeDatas = $this->initVar(EvalProfPerson::CONTRACT_TYPE);
        $this->resourcesDatas = $this->initVar(EvaluationChoices::YES_NO_IN_PROGRESS);
        $this->chargesDatas = $this->initVar(EvaluationChoices::YES_NO_IN_PROGRESS);

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

        return $datas;
    }

    protected function initVar(array $values): array
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

    protected function updateVar(SupportPerson $supportPerson, int $var = null, array $varDatas): array
    {
        if (null === $var) {
            $var = 99;
        }
        try {
            $varDatas[$var]['nbPeople'] = $varDatas[$var]['nbPeople'] + 1;

            if ($supportPerson->getHead()) {
                $varDatas[$var]['nbGroups'] = $varDatas[$var]['nbGroups'] + 1;
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $varDatas;
    }
}
