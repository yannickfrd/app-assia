<?php

namespace App\Security\Voter;

use App\Entity\Organization\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class UserVoter extends Voter
{
    use VoterTrait;

    protected $user;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DISABLE'])
            && $subject instanceof \App\Entity\Organization\User;
    }

    protected function voteOnAttribute($attribute, $user, TokenInterface $token)
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
                return true;
                break;
            case 'EDIT':
                return $this->canEdit($user);
                break;
            case 'DISABLE':
                return $this->canDisable($user);
                break;
        }

        return false;
    }

    protected function canEdit(User $user)
    {
        if ($this->userId === $user->getId()
            || $this->isAdminUser($user)
            || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function canDisable(User $user)
    {
        if ($this->isAdminUser($user)
            || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    /**
     * If current user is administrator.
     */
    protected function isAdminUser(User $user): bool
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            foreach ($this->user->getServiceUser() as $serviceCurrentUser) {
                foreach ($user->getServiceUser() as $serviceUser) {
                    if ($serviceUser->getService()->getId() === $serviceCurrentUser->getService()->getId()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
