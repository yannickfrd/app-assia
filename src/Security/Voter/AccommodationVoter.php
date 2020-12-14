<?php

namespace App\Security\Voter;

use App\Entity\Organization\Accommodation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccommodationVoter extends Voter
{
    use VoterTrait;

    protected $user;
    protected $userId;
    protected $accommodation;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE', 'DISABLE'])
            && $subject instanceof \App\Entity\Organization\Accommodation;
    }

    protected function voteOnAttribute($attribute, $accommodation, TokenInterface $token)
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        /** @var Accommodation */
        $this->accommodation = $accommodation;

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
        if ($this->isUserOfService($this->accommodation->getService())
            || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function canEdit()
    {
        if ($this->isAdminOfService($this->accommodation->getService())
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
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
