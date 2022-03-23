<?php

namespace App\Security\Voter;

use App\Entity\Event\Rdv;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RdvVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;

    protected $userId;

    /** @var Rdv */
    protected $rdv;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Event\Rdv;
    }

    protected function voteOnAttribute(string $attribute, $rdv, TokenInterface $token): bool
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        $this->rdv = $rdv;
        $this->supportGroup = $this->rdv->getSupportGroup();

        if (!$this->user) {
            return false;
        }

        switch ($attribute) {
            case 'VIEW':
                return $this->canView();
            case 'EDIT':
                return $this->canEdit();
            case 'DELETE':
                return $this->canDelete();
        }

        return false;
    }

    protected function canView(): bool
    {
        if ($this->isCreatorOrReferent()
            || $this->rdv->getUsers()->contains($this->user)
            || ($this->supportGroup && $this->isUserOfService($this->supportGroup->getService()))
            || (0 === $this->rdv->getUsers()->count() && $this->isGranted('ROLE_ADMIN'))
            || $this->isGranted('ROLE_SUPER_ADMIN')
        ) {
            return true;
        }

        return false;
    }

    protected function canEdit(): bool
    {
        return $this->canView();
    }

    protected function canDelete(): bool
    {
        return $this->canView();
    }

    protected function isCreatorOrReferent(): bool
    {
        if (($this->rdv->getCreatedBy() && $this->rdv->getCreatedBy()->getId() === $this->userId)
            || $this->supportGroup && (
                ($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->userId)
                || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->userId)
            )
        ) {
            return true;
        }

        return false;
    }
}
