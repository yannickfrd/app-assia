<?php

namespace App\Security\Voter;

use App\Entity\Organization\Pole;
use App\Entity\Organization\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PoleVoter extends Voter
{
    use VoterTrait;

    protected $user;
    protected $userId;
    protected $pole;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DISABLE'])
            && $subject instanceof \App\Entity\Organization\Pole;
    }

    protected function voteOnAttribute($attribute, $pole, TokenInterface $token)
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        /** @var Pole */
        $this->pole = $pole;

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
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if ($this->isGranted('ROLE_ADMIN') && User::STATUS_DIRECTOR === $this->user->getStatus()) {
            foreach ($this->user->getServices() as $service) {
                foreach ($this->pole->getServices() as $service) {
                    if ($service->getId() === $service->getId()) {
                        return true;
                    }
                }
            }
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
