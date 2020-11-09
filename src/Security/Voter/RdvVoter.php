<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class RdvVoter extends Voter
{
    use UserAdminOfServiceTrait;

    private $security;
    protected $currentUser;
    protected $currentUserId;
    protected $rdv;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Rdv;
    }

    protected function voteOnAttribute($attribute, $rdv, TokenInterface $token)
    {
        $this->currentUser = $token->getUser();
        $this->currentUserId = $this->currentUser->getId();
        $this->rdv = $rdv;

        if (!$this->currentUser) {
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
        }

        return false;
    }

    protected function canView()
    {
        if ($this->currentUserId == $this->rdv->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->currentUserId == $this->rdv->getSupportGroup()->getReferent()->getId()
        ) {
            return true;
        }

        $user = $this->rdv->getCreatedBy();
        foreach ($this->currentUser->getServiceUser() as $serviceCurrentUser) {
            foreach ($user->getServiceUser() as $serviceUser) {
                if ($serviceCurrentUser->getService()->getId() == $serviceUser->getService()->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function canEdit()
    {
        if ($this->currentUserId == $this->rdv->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->isAdminUserOfService($this->rdv->getSupportGroup()->getService())
            || $this->currentUserId == $this->rdv->getSupportGroup()->getReferent()->getId()
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete()
    {
        if ($this->currentUserId == $this->rdv->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->isAdminUserOfService($this->rdv->getSupportGroup()->getService())
            || $this->currentUserId == $this->rdv->getSupportGroup()->getReferent()->getId()
        ) {
            return true;
        }

        return false;
    }
}
