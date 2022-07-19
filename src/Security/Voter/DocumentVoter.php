<?php

namespace App\Security\Voter;

use App\Entity\Organization\User;
use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class DocumentVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;

    /** @var Document */
    protected $document;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Support\Document;
    }

    protected function voteOnAttribute(string $attribute, $document, TokenInterface $token): bool
    {
        $this->user = $token->getUser();
        $this->document = $document;
        $this->supportGroup = $this->document->getSupportGroup();

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
            || $this->isUserOfService($this->supportGroup->getService())
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
        if (($this->document->getCreatedBy() && $this->document->getCreatedBy()->getId() === $this->user->getId())
            || ($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->user->getId())
            || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->user->getId())
        ) {
            return true;
        }

        return false;
    }
}
