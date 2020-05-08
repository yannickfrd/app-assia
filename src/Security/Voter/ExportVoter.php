<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ExportVoter extends Voter
{
    use UserIsAdminTrait;

    private $security;
    protected $currentUser;
    protected $currentUserId;
    protected $export;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['GET', 'VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Export;
    }

    protected function voteOnAttribute($attribute, $export, TokenInterface $token)
    {
        $this->currentUser = $token->getUser();
        $this->currentUserId = $this->currentUser->getId();
        $this->export = $export;

        if (!$this->currentUser) {
            return false;
        }

        switch ($attribute) {
            case 'GET':
                return $this->canView();
                break;
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
        if ($this->currentUserId == $this->export->getCreatedBy()->getId()) {
            return true;
        }

        $user = $this->export->getCreatedBy();
        foreach ($this->currentUser->getServiceUser() as $serviceCurrentUser) {
            foreach ($user->getServiceUser() as $serviceUser) {
                if ($serviceCurrentUser->getService()->getId() == $serviceUser->getService()->getId()) {
                    return true;
                }
            }
        }
        if ($this->security->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    protected function canEdit()
    {
        if ($this->currentUserId == $this->export->getCreatedBy()->getId()) {
            return true;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || ($this->userIsAdmin($this->export->getCreatedBy()))) {
            return true;
        }

        return false;
    }

    protected function canDelete()
    {
        if ($this->currentUserId == $this->export->getCreatedBy()->getId()) {
            return true;
        }

        if ($this->security->isGranted('ROLE_SUPER_ADMIN') || ($this->userIsAdmin($this->export->getCreatedBy()))) {
            return true;
        }

        return false;
    }
}
