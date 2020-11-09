<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class DocumentVoter extends Voter
{
    use UserAdminOfServiceTrait;

    private $security;
    protected $currentUser;
    protected $currentUserId;
    protected $document;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Document;
    }

    protected function voteOnAttribute($attribute, $document, TokenInterface $token): bool
    {
        $this->currentUser = $token->getUser();
        $this->currentUserId = $this->currentUser->getId();
        $this->document = $document;

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

    protected function canView(): bool
    {
        if ($this->currentUserId == $this->document->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->currentUserId == $this->document->getSupportGroup()->getReferent()->getId()) {
            return true;
        }

        $user = $this->document->getCreatedBy();
        foreach ($this->currentUser->getServiceUser() as $serviceCurrentUser) {
            foreach ($user->getServiceUser() as $serviceUser) {
                if ($serviceCurrentUser->getService()->getId() == $serviceUser->getService()->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function canEdit(): bool
    {
        if ($this->currentUserId == $this->document->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->isAdminUserOfService($this->document->getSupportGroup()->getService())
            || $this->currentUserId == $this->document->getSupportGroup()->getReferent()->getId()
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete(): bool
    {
        if ($this->currentUserId == $this->document->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->isAdminUserOfService($this->document->getSupportGroup()->getService())
            || $this->currentUserId == $this->document->getSupportGroup()->getReferent()->getId()
        ) {
            return true;
        }

        return false;
    }
}
