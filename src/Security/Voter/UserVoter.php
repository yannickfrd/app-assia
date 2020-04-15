<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\Voter\UserIsAdminTrait;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
    use UserIsAdminTrait;

    private $security;
    protected $currentUser;
    protected $currentUserId;
    protected $user;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DISABLE'])
            && $subject instanceof \App\Entity\User;
    }

    protected function voteOnAttribute($attribute, $user, TokenInterface $token)
    {
        $this->currentUser = $token->getUser();
        $this->currentUserId = $this->currentUser->getId();
        $this->user = $user;

        if (!$this->currentUser) {
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
        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || $this->userIsAdmin($this->user)) {
            return true;
        }

        if ($this->currentUser->getId() == $this->user->getId()) {
            return true;
        }

        return false;
    }

    protected function canDisable()
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || ($this->userIsAdmin($this->user))) {
            return true;
        }

        return false;
    }
}
