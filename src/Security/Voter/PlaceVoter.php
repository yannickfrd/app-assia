<?php

namespace App\Security\Voter;

use App\Entity\Organization\Place;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlaceVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;
    protected $userId;
    protected $place;
    
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE', 'DISABLE'])
        && $subject instanceof \App\Entity\Organization\Place;
    }
    
    protected function voteOnAttribute($attribute, $place, TokenInterface $token)
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        /** @var Place */
        $this->place = $place;

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
            case 'DISABLE':
                return $this->canDelete();
                break;
        }

        return false;
    }

    protected function canView()
    {
        if ($this->isUserOfService($this->place->getService())
            || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function canEdit()
    {
        if ($this->isAdminOfService($this->place->getService())
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
}
