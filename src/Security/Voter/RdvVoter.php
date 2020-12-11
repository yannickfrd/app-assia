<?php

namespace App\Security\Voter;

use App\Entity\Rdv;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RdvVoter extends Voter
{
    use VoterTrait;

    protected $user;
    protected $userId;
    protected $rdv;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Rdv;
    }

    protected function voteOnAttribute($attribute, $rdv, TokenInterface $token)
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        /** @var Rdv */
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

    protected function canView()
    {
        if ($this->isCreatorOrReferent()
            || ($this->supportGroup && $this->isUserOfService($this->supportGroup->getService()))
            || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canEdit()
    {
        if ($this->isCreatorOrReferent()
            || $this->isAdminOfService($this->supportGroup->getService())
            || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete()
    {
        return $this->canEdit();
    }

    protected function isCreatorOrReferent(): bool
    {
        if (($this->rdv->getCreatedBy() && $this->rdv->getCreatedBy()->getId() === $this->userId)
            || ($this->supportGroup && ($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->userId))
            || ($this->supportGroup && ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->userId))
        ) {
            return true;
        }
        
        return false;
    }

}
