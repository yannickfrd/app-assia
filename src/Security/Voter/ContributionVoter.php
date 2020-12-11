<?php

namespace App\Security\Voter;

use App\Entity\Contribution;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ContributionVoter extends Voter
{
    use VoterTrait;

    protected $user;
    protected $userId;
    protected $contribution;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Contribution;
    }

    protected function voteOnAttribute($attribute, $contribution, TokenInterface $token): bool
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        /** @var Contribution */
        $this->contribution = $contribution;
        $this->supportGroup = $this->contribution->getSupportGroup();

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
        }

        return false;
    }

    protected function canView(): bool
    {
        if ($this->isCreatorOrReferent()
            || $this->isUserOfService($this->supportGroup->getService())
            || $this->isGranted('ROLE_SUPER_ADMIN')
            ) {
            return true;
        }

        return false;
    }

    protected function canEdit(): bool
    {
        if ($this->isCreatorOrReferent()
            || $this->isAdminOfService($this->supportGroup->getService())
            || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canDelete(): bool
    {
        return $this->canEdit();
    }

    protected function isCreatorOrReferent(): bool
    {
        if (($this->contribution->getCreatedBy() && $this->contribution->getCreatedBy()->getId() === $this->userId)
            || ($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->userId)
            || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->userId)
        ) {
            return true;
        }

        return false;
    }
}
