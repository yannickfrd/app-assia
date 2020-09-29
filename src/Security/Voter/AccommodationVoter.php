<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AccommodationVoter extends Voter
{
    private $security;
    protected $user;
    protected $userId;
    protected $accommodation;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE', 'DISABLE'])
            && $subject instanceof \App\Entity\Accommodation;
    }

    protected function voteOnAttribute($attribute, $accommodation, TokenInterface $token)
    {
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        $this->accommodation = $accommodation;

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
                return $this->canView();
                break;
            case 'EDIT':
                return $this->canEdit();
                break;
            case 'DELETE':
                return $this->canDelete();
                break;
            case 'DISABLE':
                return $this->canDelete();
                break;
        }

        return false;
    }

    protected function canView()
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || ($this->isAdminService())) {
            return true;
        }

        foreach ($this->user->getServiceUser() as $serviceUser) {
            if ($this->accommodation->getService()->getId() == $serviceUser->getService()->getId()) {
                return true;
            }
        }

        return false;
    }

    protected function canEdit()
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || $this->isAdminService()) {
            return true;
        }

        if (($this->accommodation->getReferent() && $this->accommodation->getReferent()->getId() == $this->userId)
            || ($this->accommodation->getReferent2() && $this->accommodation->getReferent2()->getId() == $this->userId)
            || ($this->accommodation->getCreatedBy() && $this->accommodation->getCreatedBy()->getId() == $this->userId)
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete()
    {
        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || ($this->isAdminService())) {
            return true;
        }

        // if (($this->accommodation->getReferent() && $this->accommodation->getReferent()->getId() == $this->userId)
        //     || ($this->accommodation->getReferent2() && $this->accommodation->getReferent2()->getId() == $this->userId)
        //     || ($this->accommodation->getCreatedBy() && $this->accommodation->getCreatedBy()->getId() == $this->userId)
        // ) {
        //     return true;
        // }
        return false;
    }

    // If current user is administrator
    protected function isAdminService()
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            foreach ($this->user->getServiceUser() as $serviceUser) {
                if ($this->accommodation->getService()->getId() == $serviceUser->getService()->getId()) {
                    return true;
                }
            }
        }

        return false;
    }
}
