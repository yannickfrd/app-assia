<?php

namespace App\Security\Voter;

use App\Entity\Admin\Export;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExportVoter extends Voter
{
    use VoterTrait;

    protected $user;
    protected $userId;
    protected $export;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['GET', 'VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Admin\Export;
    }

    protected function voteOnAttribute($attribute, $export, TokenInterface $token)
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        /** @var Export */
        $this->export = $export;

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case 'GET':
                return $this->canView();
                break;
            case 'VIEW':
                return $this->canView();
                break;
            case 'DELETE':
                return $this->canDelete();
                break;
        }

        return false;
    }

    protected function canView()
    {
        if ($this->userId === $this->export->getCreatedBy()->getId()
         || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete()
    {
        return $this->canView();
    }
}
