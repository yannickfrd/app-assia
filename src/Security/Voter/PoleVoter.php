<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PoleVoter extends Voter
{
    private $security;
    protected $currentUser;
    protected $currentUserId;
    protected $pole;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ["VIEW", "EDIT", "DESACTIVATE"])
            && $subject instanceof \App\Entity\Pole;
    }

    protected function voteOnAttribute($attribute, $pole, TokenInterface $token)
    {
        $this->currentUser = $token->getUser();
        $this->currentUserId = $this->currentUser->getId();
        $this->pole = $pole;

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
        if ($this->security->isGranted("ROLE_SUPER_ADMIN")) {
            return true;
        }

        if ($this->security->isGranted("ROLE_ADMIN") && $this->currentUser->getStatus() == 4) {
            foreach ($this->currentUser->getServiceUser() as $serviceUser) {
                foreach ($this->pole->getServices() as $service) {
                    if ($service->getId() == $serviceUser->getService()->getId()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    protected function canDeactivate()
    {
        if ($this->security->isGranted("ROLE_SUPER_ADMIN")) {
            return true;
        }
        return false;
    }
}
