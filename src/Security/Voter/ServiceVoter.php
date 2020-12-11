<?php

namespace App\Security\Voter;

use App\Entity\Service;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ServiceVoter extends Voter
{
    use VoterTrait;

    protected $user;
    protected $service;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DISABLE'])
            && $subject instanceof \App\Entity\Service;
    }

    protected function voteOnAttribute($attribute, $service, TokenInterface $token)
    {
        /** @var User */
        $this->user = $token->getUser();
        /** @var Service */
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

    protected function canEdit()
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
