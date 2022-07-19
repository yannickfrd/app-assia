<?php

namespace App\Security\Voter;

use App\Entity\Event\Task;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TaskVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;

    /** @var Task */
    protected $task;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Event\Task;
    }

    protected function voteOnAttribute(string $attribute, $task, TokenInterface $token): bool
    {
        $this->user = $token->getUser();
        $this->task = $task;
        $this->supportGroup = $this->task->getSupportGroup();

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
                return $this->canView();
                break;
            case 'EDIT':
                return $this->canEdit();
                break;
            case 'DELETE':
                return $this->canDelete();
                break;
        }

        return false;
    }

    protected function canView(): bool
    {
        if ($this->isCreatorOrReferent()
            || ($this->supportGroup && $this->isUserOfService($this->supportGroup->getService()))
            || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canEdit(): bool
    {
        return $this->canView();
    }

    protected function canDelete(): bool
    {
        return $this->canView();
    }

    protected function isCreatorOrReferent(): bool
    {
        if (($this->task->getCreatedBy() && $this->task->getCreatedBy()->getId() === $this->user->getId())
            || $this->task->getUsers()->contains($this->user)
            || $this->supportGroup && (($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->user->getId())
            || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->user->getId()))
        ) {
            return true;
        }

        return false;
    }
}
