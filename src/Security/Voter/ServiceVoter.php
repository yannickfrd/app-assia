<?php

namespace App\Security\Voter;

use App\Entity\Organization\Service;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ServiceVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;
    
    /** @var Service */
    protected $service;

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DISABLE'])
            && $subject instanceof \App\Entity\Organization\Service;
    }

    protected function voteOnAttribute($attribute, $service, TokenInterface $token): bool
    {
        $this->user = $token->getUser();
        $this->service = $service;

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
                return true;
                break;
            case 'EDIT':
                return $this->canEdit();
                break;
            case 'DISABLE':
                return $this->canDisable();
                break;
        }

        return false;
    }

    protected function canEdit(): bool
    {
        if ($this->isAdminOfService($this->service)
            || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function canDisable()
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }
}
