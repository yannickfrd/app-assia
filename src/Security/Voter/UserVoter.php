<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UserVoter extends Voter
{
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
        return in_array($attribute, ["VIEW", "EDIT", "DESACTIVATE"])
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
            case "VIEW":
                return true;
                break;
            case "EDIT":
                return $this->canEdit();
                break;
            case "DEACTIVATE":
                return $this->canDeactivate();
                break;
        }
        return false;
    }

    protected function canEdit()
    {
        if ($this->security->isGranted("ROLE_SUPER_ADMIN") || $this->isAdminUser()) {
            return true;
        }

        if ($this->currentUser->getId() == $this->user->getId()) {
            return true;
        }
        return false;
    }

    protected function canDeactivate()
    {
        if ($this->security->isGranted("ROLE_SUPER_ADMIN") || ($this->isAdminUser())) {
            return true;
        }
        return false;
    }

    // If current user is administrator
    protected function isAdminUser()
    {
        if ($this->security->isGranted("ROLE_ADMIN")) {
            foreach ($this->currentUser->getServiceUser() as $serviceCurrentUser) {
                foreach ($this->user->getServiceUser() as $serviceUser) {
                    if ($serviceCurrentUser->getService() && $serviceCurrentUser->getService()->getId() == $serviceUser->getService()->getId()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}
