<?php

namespace App\EventDispatcher\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\SupportGroup;
use App\Event\Evaluation\EvaluationEvent;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Security;

class EvaluationSubscriber implements EventSubscriberInterface
{
    private $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'evaluation.before_create' => 'beforeUpdate',
            'evaluation.after_create' => 'discache',
            'evaluation.before_update' => 'beforeUpdate',
            'evaluation.after_update' => 'discache',
        ];
    }

    public function beforeUpdate(EvaluationEvent $event): void
    {
        $evaluationGroup = $event->getEvaluationGroup();
        $supportGroup = $event->getSupportGroup();

        $evaluationGroup
            ->setUpdatedAt($now = new \DateTime())
            ->setUpdatedBy($this->user);

        $supportGroup
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->user);

        $supportGroup->setUpdatedBy($this->user);

        $this->updateBudgetGroup($evaluationGroup);
    }

    public function discache(EvaluationEvent $event): bool
    {
        $evaluationGroup = $event->getEvaluationGroup();
        $supportGroupId = $evaluationGroup->getSupportGroup()->getId();
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        return $cache->deleteItems([
            EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroupId,
            SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroupId,
        ]);
    }

    /**
     * Met à jour le budget du groupe.
     */
    protected function updateBudgetGroup(EvaluationGroup $evaluationGroup): void
    {
        $resourcesGroupAmt = 0;
        $chargesGroupAmt = 0;
        $debtsGroupAmt = 0;
        // Ressources et dettes initiales
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

        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();
        $evalBudgetGroup->setResourcesGroupAmt($resourcesGroupAmt);
        $evalBudgetGroup->setChargesGroupAmt($chargesGroupAmt);
        $evalBudgetGroup->setDebtsGroupAmt($debtsGroupAmt);
        $budgetBalanceAmt = $resourcesGroupAmt - $chargesGroupAmt - $evalBudgetGroup->getContributionAmt();
        $evalBudgetGroup->setBudgetBalanceAmt($budgetBalanceAmt);
        // Ressources et dettes initiales
        $evaluationGroup->getEvalInitGroup()->setResourcesGroupAmt($evalInitResourcesGroupAmt);
        $evaluationGroup->getEvalInitGroup()->setDebtsGroupAmt($initDebtsGroupAmt);
    }
}
