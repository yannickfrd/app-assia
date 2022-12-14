<?php

namespace App\Security\Voter;

use App\Entity\Organization\Place;
use App\Entity\Organization\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PlaceVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;

    /** @var Place */
    protected $place;

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE', 'DISABLE'])
        && $subject instanceof \App\Entity\Organization\Place;
    }

    protected function voteOnAttribute(string $attribute, $place, TokenInterface $token): bool
    {
        $this->user = $token->getUser();
        $this->place = $place;

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
                return $this->canView();
            case 'EDIT':
                return $this->canEdit();
            case 'DISABLE':
            case 'DELETE':
                return $this->canDelete();
        }

        return false;
    }

    protected function canView(): bool
    {
        if ($this->isUserOfService($this->place->getService())
            || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function canEdit(): bool
    {
        if ($this->isAdminOfService($this->place->getService())
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
}
