<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class NoteVoter extends Voter
{
    use UserIsAdminTrait;

    private $security;
    protected $currentUser;
    protected $currentUserId;
    /**
     * @var Note
     */
    protected $note;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Note;
    }

    protected function voteOnAttribute($attribute, $note, TokenInterface $token)
    {
        $this->currentUser = $token->getUser();
        $this->currentUserId = $this->currentUser->getId();
        $this->note = $note;

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
        if ($this->currentUserId == $this->note->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->currentUserId == $this->note->getSupportGroup()->getReferent()->getId()
        ) {
            return true;
        }

        $user = $this->note->getCreatedBy();
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
        if ($this->currentUserId == $this->note->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->userIsAdmin($this->note->getCreatedBy())
            || $this->currentUserId == $this->note->getSupportGroup()->getReferent()->getId()
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete()
    {
        if ($this->currentUserId == $this->note->getCreatedBy()->getId()
            || $this->security->isGranted('ROLE_SUPER_ADMIN')
            || $this->userIsAdmin($this->note->getCreatedBy())
            || $this->currentUserId == $this->note->getSupportGroup()->getReferent()->getId()
        ) {
            return true;
        }

        return false;
    }
}
