<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ServiceVoter extends Voter
{
    private $security;
    protected $currentUser;
    protected $currentUserId;
    protected $service;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ["VIEW", "EDIT", "DESACTIVATE"])
            && $subject instanceof \App\Entity\Service;
    }

    protected function voteOnAttribute($attribute, $service, TokenInterface $token)
    {
        $this->currentUser = $token->getUser();
        $this->currentUserId = $this->currentUser->getId();
        $this->service = $service;

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
        if ($this->security->isGranted("ROLE_ADMIN")) {
            foreach ($this->currentUser->getServiceUser() as $serviceUser) {
                if ($serviceUser->getService() && $serviceUser->getService()->getId() == $this->service->getId()) {
                    return true;
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
