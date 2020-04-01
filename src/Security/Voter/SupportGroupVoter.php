<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class SupportGroupVoter extends Voter
{
    private $security;
    protected $user;
    protected $userId;
    protected $supportGroup;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ["VIEW", "EDIT", "DELETE"])
            && $subject instanceof \App\Entity\SupportGroup;
    }

    protected function voteOnAttribute($attribute, $supportGroup, TokenInterface $token)
    {
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        $this->supportGroup = $supportGroup;

        // dd($this->user);

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case "VIEW":
                return $this->canView();
                break;
            case "EDIT":
                return $this->canView();
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

        foreach ($this->user->getServiceUser() as $serviceUser) {
            if ($this->supportGroup->getService()->getId() == $serviceUser->getService()->getId()) {
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

        if (($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() == $this->userId)
            || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() == $this->userId)
            || ($this->supportGroup->getCreatedBy() && $this->supportGroup->getCreatedBy()->getId() == $this->userId)
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

        // if (($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() == $this->userId)
        //     || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() == $this->userId)
        //     || ($this->supportGroup->getCreatedBy() && $this->supportGroup->getCreatedBy()->getId() == $this->userId)
        // ) {
        //     return true;
        // }
        return false;
    }

    // If current user is administrator
    protected function isAdminService()
    {
        if ($this->security->isGranted("ROLE_ADMIN")) {
            foreach ($this->user->getServiceUser() as $serviceUser) {
                if ($this->supportGroup->getService()->getId() == $serviceUser->getService()->getId()) {
                    return true;
                }
            }
        }
        return false;
    }
}
