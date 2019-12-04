<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SupportGrpVoter extends Voter
{
    private $security;
    protected $user;
    protected $userId;
    protected $supportGrp;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ["VIEW", "EDIT", "DELETE"])
            && $subject instanceof \App\Entity\SupportGrp;
    }

    protected function voteOnAttribute($attribute, $supportGrp, TokenInterface $token)
    {
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        $this->supportGrp = $supportGrp;

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case "VIEW":
                return $this->canView();
                break;
            case "EDIT":
                return $this->canEdit();
                break;
            case "DELETE":
                return $this->canDelete();
                break;
        }
        return false;
    }

    protected function canView()
    {
        if ($this->security->isGranted("ROLE_SUPER_ADMIN") || ($this->isAdminService())) {
            return true;
        }

        foreach ($this->user->getRoleUser() as $role) {
            if ($this->supportGrp->getService()->getId() == $role->getService()->getId()) {
                return true;
            }
        }
        return false;
    }

    protected function canEdit()
    {
        if ($this->security->isGranted("ROLE_SUPER_ADMIN") || $this->isAdminService()) {
            return true;
        }

        if (($this->supportGrp->getReferent() && $this->supportGrp->getReferent()->getId() == $this->userId)
            || ($this->supportGrp->getReferent2() && $this->supportGrp->getReferent2()->getId() == $this->userId)
            || ($this->supportGrp->getCreatedBy()->getId() == $this->userId)
        ) {
            return true;
        }
        return false;
    }

    protected function canDelete()
    {
        if ($this->security->isGranted("ROLE_SUPER_ADMIN") || ($this->isAdminService())) {
            return true;
        }
        return false;
    }

    // Is administrator of service
    protected function isAdminService()
    {
        if ($this->security->isGranted("ROLE_ADMIN")) {
            foreach ($this->user->getRoleUser() as $role) {
                if ($this->supportGrp->getService()->getId() == $role->getService()->getId()) {
                    return true;
                }
            }
        }
        return false;
    }
}
