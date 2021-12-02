<?php

namespace App\Security\Voter;

use App\Entity\Support\Rdv;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RdvVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;
    
    protected $userId;

    /** @var Rdv */
    protected $rdv;
    
    /** @var SupportGroup */
    protected $supportGroup;
    
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Support\Rdv;
    }

    protected function voteOnAttribute($attribute, $rdv, TokenInterface $token): bool
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        $this->rdv = $rdv;
        $this->supportGroup = $this->rdv->getSupportGroup();

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
        if ($this->isCreatorOrReferent()
            || ($this->supportGroup && $this->isAdminOfService($this->supportGroup->getService()))
            || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete(): bool
    {
        return $this->canEdit();
    }

    protected function isCreatorOrReferent(): bool
    {
        if (($this->rdv->getCreatedBy() && $this->rdv->getCreatedBy()->getId() === $this->userId)
            || $this->supportGroup && (($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->userId)
            || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->userId))
        ) {
            return true;
        }

        return false;
    }
}
