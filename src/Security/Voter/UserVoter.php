<?php

namespace App\Security\Voter;

use App\Entity\Organization\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DISABLE'])
            && $subject instanceof \App\Entity\Organization\User;
    }

    protected function voteOnAttribute(string $attribute, $user, TokenInterface $token): bool
    {
        $this->user = $token->getUser();

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
                return $this->canView($user);
            case 'EDIT':
                return $this->canEdit($user);
            case 'DISABLE':
                return $this->canDisable($user);
        }

        return false;
    }

    protected function canView(User $user): bool
    {
        return $this->canEdit($user)
            || ($this->isGranted('ROLE_ADMIN') && !$user->hasRole('ROLE_ADMIN') && !$user->hasRole('ROLE_SUPER_ADMIN'))
        ;
    }

    protected function canEdit(User $user): bool
    {
        return null === $user->getId()
            || $this->user->getId() === $user->getId()
            || $this->isGranted('ROLE_SUPER_ADMIN')
            || $this->isAdminUser($user)
        ;
    }

    protected function canDisable(User $user): bool
    {
        return $this->isAdminUser($user) || $this->isGranted('ROLE_SUPER_ADMIN');
    }

    /**
     * If current user is administrator.
     */
    protected function isAdminUser(User $user): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            foreach ($this->user->getServices() as $serviceCurrentUser) {
                foreach ($user->getServices() as $service) {
                    if ($service->getId() === $serviceCurrentUser->getId()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
