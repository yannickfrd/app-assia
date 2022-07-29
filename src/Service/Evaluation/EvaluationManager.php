<?php

namespace App\Service\Evaluation;

use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Service\Grammar;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;

class EvaluationManager extends EvaluationCreator
{
    private $security;
    private $em;
    private $evaluationCompletionChecker;

    private $flashBag;

    public function __construct(
        Security $security,
        EntityManagerInterface $em,
        EvaluationCompletionChecker $evaluationCompletionChecker,
        RequestStack $requestStack
    ) {
        $this->security = $security;
        $this->em = $em;
        $this->evaluationCompletionChecker = $evaluationCompletionChecker;

        /** @var Session */
        $session = $requestStack->getSession();
        $flashBag = $session->getFlashBag();
        $this->flashBag = $flashBag;

        parent::__construct($em);
    }

    public function updateAndFlush(EvaluationGroup $evaluationGroup): void
    {
        /** @var User */
        $user = $this->security->getUser();

        $evaluationGroup
            ->setUpdatedAt($now = new \DateTime())
            ->setUpdatedBy($user);

        $evaluationGroup->getSupportGroup()
            ->setEvaluationScore($this->evaluationCompletionChecker->getScore($evaluationGroup)['score'])
            ->setUpdatedAt($now)
            ->setUpdatedBy($user);

        $this->updateBudgetGroup($evaluationGroup);

        $this->deleteCacheItems($evaluationGroup);

        $this->em->flush();
    }

    public static function deleteCacheItems(EvaluationGroup $evaluationGroup): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $supportGroup = $evaluationGroup->getSupportGroup();

        if ($supportGroup->getReferent()) {
            $cache->deleteItem(User::CACHE_USER_SUPPORTS_KEY.$supportGroup->getReferent()->getId());
        }

        return $cache->deleteItems([
            EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(),
            SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId(),
        ]);
    }

    protected function updateBudgetGroup(EvaluationGroup $evaluationGroup): void
    {
        $resourcesGroupAmt = 0;
        $chargesGroupAmt = 0;
        $debtsGroupAmt = 0;

        $evalInitResourcesGroupAmt = 0;
        $initDebtsGroupAmt = 0;

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
            if ($evalBudgetPerson) {
                $resourcesGroupAmt += $evalBudgetPerson->getResourcesAmt();
                $chargesGroupAmt += $evalBudgetPerson->getChargesAmt();
                $debtsGroupAmt += $evalBudgetPerson->getDebtsAmt();
            }

            $evalInitPerson = $evaluationPerson->getEvalInitPerson();
            if ($evalInitPerson) {
                $evalInitResourcesGroupAmt += $evalInitPerson->getResourcesAmt();
                $initDebtsGroupAmt += $evalInitPerson->getDebtsAmt();
            }
        }

        if (0 === $resourcesGroupAmt && 0 === $chargesGroupAmt && 0 === $debtsGroupAmt) {
            return;
        }

        $evalBudgetGroup = (new EvalBudgetGroup())
            ->setEvaluationGroup($evaluationGroup);

        $evalBudgetGroup->setResourcesGroupAmt($resourcesGroupAmt);
        $evalBudgetGroup->setChargesGroupAmt($chargesGroupAmt);
        $evalBudgetGroup->setDebtsGroupAmt($debtsGroupAmt);
        $budgetBalanceAmt = $resourcesGroupAmt - $chargesGroupAmt - $evalBudgetGroup->getContributionAmt();
        $evalBudgetGroup->setBudgetBalanceAmt($budgetBalanceAmt);

        $evaluationGroup->getEvalInitGroup()->setResourcesGroupAmt($evalInitResourcesGroupAmt);
        $evaluationGroup->getEvalInitGroup()->setDebtsGroupAmt($initDebtsGroupAmt);
    }

    public function addEvaluationPeople(EvaluationGroup $evaluationGroup): void
    {
        $supportGroup = $evaluationGroup->getSupportGroup();
        $supportPeople = $supportGroup->getSupportPeople();

        if ($evaluationGroup->getEvaluationPeople()->count() !== $supportPeople->count()) {
            foreach ($supportPeople as $supportPerson) {
                if (false === $this->personIsInEvaluation($supportPerson, $evaluationGroup)) {
                    $person = $supportPerson->getPerson();
                    $this->createEvaluationPerson($supportPerson, $evaluationGroup);

                    $this->flashBag->add('success', "{$person->getFullname()} a été ajouté".
                        Grammar::gender($person->getGender())." à l'évaluation sociale.");
                }
            }
        }

        $this->em->flush();
    }

    public static function personIsInEvaluation(SupportPerson $supportPerson, EvaluationGroup $evaluationGroup): bool
    {
        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            if ($supportPerson->getId() === $evaluationPerson->getSupportPerson()->getId()) {
                return true;
            }
        }

        return false;
    }

    public static function personIsInSupport(EvaluationPerson $evaluationPerson, SupportGroup $supportGroup): bool
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getId() === $evaluationPerson->getSupportPerson()->getId()) {
                return true;
            }
        }

        return false;
    }
}
