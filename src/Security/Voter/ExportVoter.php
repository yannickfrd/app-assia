<?php

namespace App\Security\Voter;

use App\Entity\Admin\Export;
use App\Entity\Organization\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ExportVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;

    /** @var Export */
    protected $export;

    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, ['GET', 'VIEW', 'DELETE'])
            && $subject instanceof Export;
    }

    protected function voteOnAttribute($attribute, $export, TokenInterface $token): bool
    {
        $this->user = $token->getUser();
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

    protected function canView(): bool
    {
        if ($this->user->getId() === $this->export->getCreatedBy()->getId()
         || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete(): bool
    {
        return $this->canView();
    }
}
