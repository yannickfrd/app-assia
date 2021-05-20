<?php

namespace App\Security\Voter;

use App\Entity\Support\SupportGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class SupportGroupVoter extends Voter
{
    use VoterTrait;

    protected $user;
    protected $userId;
    protected $supportGroup;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Support\SupportGroup;
    }

    protected function voteOnAttribute($attribute, $supportGroup, TokenInterface $token)
    {
        /* @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        /* @var SupportGroup */
        $this->supportGroup = $supportGroup;

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

    protected function canView()
    {
        if ($this->isCreatorOrReferent()
            || $this->isUserOfService($this->supportGroup->getService())
            || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canEdit()
    {
        return $this->canView();
    }

    protected function canDelete()
    {
        if ($this->isAdminOfService($this->supportGroup->getService())
            || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function isCreatorOrReferent(): bool
    {
        if (($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->userId)
            || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->userId)
            || ($this->supportGroup->getCreatedBy() && $this->supportGroup->getCreatedBy()->getId() === $this->userId)
        ) {
            return true;
        }

        return false;
    }
}
