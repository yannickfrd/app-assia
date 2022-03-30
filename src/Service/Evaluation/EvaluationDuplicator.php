<?php

namespace App\Service\Evaluation;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalInitGroup;
use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Evaluation\EvalInitResource;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Organization\Service;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\ORM\EntityManagerInterface;

class EvaluationDuplicator
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Clone the evaluation of the group.
     */
    public function cloneEvaluationGroup(SupportGroup $supportGroup, EvaluationGroup $evaluationGroup): EvaluationGroup
    {
        $newEvaluationGroup = (new EvaluationGroup())
            ->setDate(new \DateTime())
            ->setEvalInitGroup(new EvalInitGroup())
        ;

        if ($evaluationGroup->getEvalBudgetGroup()) {
            $newEvaluationGroup->setEvalBudgetGroup(clone $evaluationGroup->getEvalBudgetGroup());
        }
        if ($evaluationGroup->getEvalFamilyGroup()) {
            $newEvaluationGroup->setEvalFamilyGroup(clone $evaluationGroup->getEvalFamilyGroup());
        }
        if ($evaluationGroup->getEvalHousingGroup()) {
            $newEvaluationGroup->setEvalHousingGroup(clone $evaluationGroup->getEvalHousingGroup());
        }
        if ($evaluationGroup->getEvalSocialGroup()) {
            $newEvaluationGroup->setEvalSocialGroup(clone $evaluationGroup->getEvalSocialGroup());
        }
        if (Service::SERVICE_TYPE_HOTEL === $supportGroup->getService()->getType()
            && $evaluationGroup->getEvalHotelLifeGroup()) {
            $newEvaluationGroup->setEvalHotelLifeGroup(clone $evaluationGroup->getEvalHotelLifeGroup());
        }

        return $newEvaluationGroup;
    }

    public function createEvalInitGroup(SupportGroup $supportGroup, EvaluationGroup $evaluationGroup): EvalInitGroup
    {
        $evalHousingGroup = $evaluationGroup->getEvalHousingGroup();
        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();

        $evalInitGroup = (new EvalInitGroup())
            ->setHousingStatus($evalHousingGroup->getHousingStatus())
            ->setSiaoRequest($evalHousingGroup->getSiaoRequest())
            ->setSocialHousingRequest($evalHousingGroup->getSocialHousingRequest())
            ->setResourcesGroupAmt($evalBudgetGroup->getResourcesGroupAmt())
            ->setDebtsGroupAmt($evalBudgetGroup->getDebtsGroupAmt())
            ->setSupportGroup($supportGroup)
        ;

        $this->em->persist($evalInitGroup);

        $evaluationGroup->setEvalInitGroup($evalInitGroup);

        return $evalInitGroup;
    }

    public function createEvalInitPerson(SupportPerson $supportPerson, EvaluationPerson $evaluationPerson): ?EvalInitPerson
    {
        $evalAdmPerson = $evaluationPerson->getEvalAdmPerson();
        $evalSocialPerson = $evaluationPerson->getEvalSocialPerson();
        $evalProfPerson = $evaluationPerson->getEvalProfPerson();
        $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();

        $evalInitPerson = (new EvalInitPerson())
            ->setPaper($evalAdmPerson->getPaper())
            ->setPaperType($evalAdmPerson->getPaperType())
            ->setRightSocialSecurity($evalSocialPerson->getRightSocialSecurity())
            ->setSocialSecurity($evalSocialPerson->getSocialSecurity())
            ->setFamilyBreakdown($evalSocialPerson->getFamilyBreakdown())
            ->setFriendshipBreakdown($evalSocialPerson->getFriendshipBreakdown())
            ->setSupportPerson($supportPerson)
        ;

        if ($evalProfPerson) {
            $evalInitPerson
                ->setProfStatus($evalProfPerson->getProfStatus())
                ->setContractType($evalProfPerson->getContractType())
            ;
        }

        if ($evalBudgetPerson) {
            $evalInitPerson
                ->setResource($evalBudgetPerson->getResource())
                ->setResourcesAmt($evalBudgetPerson->getResourcesAmt())
                ->setRessourceOtherPrecision($evalBudgetPerson->getRessourceOtherPrecision())
                ->setDebt($evalBudgetPerson->getDebt())
                ->setDebtsAmt($evalBudgetPerson->getDebtsAmt())
            ;

            $this->createEvalInitResources($evalBudgetPerson, $evalInitPerson);
        }

        $this->em->persist($evalInitPerson);

        $evaluationPerson->setEvalInitPerson($evalInitPerson);

        return $evalInitPerson;
    }

    /**
     * Dupplique les ressources de la situation budgétaire dans la situation initiale.
     */
    private function createEvalInitResources(EvalBudgetPerson $evalBudgetPerson, EvalInitPerson $evalInitPerson): EvalInitPerson
    {
        foreach ($evalBudgetPerson->getEvalBudgetResources() as $evalBudgetResource) {
            $evalInitResource = (new EvalInitResource())
                ->setEvalInitPerson($evalInitPerson)
                ->setType($evalBudgetResource->getType())
                ->setAmount($evalBudgetResource->getAmount())
                ->setComment($evalBudgetResource->getComment())
            ;

            $this->em->persist($evalInitResource);
        }

        return $evalInitPerson;
    }
}
