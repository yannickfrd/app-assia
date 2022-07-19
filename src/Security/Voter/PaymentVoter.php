<?php

namespace App\Security\Voter;

use App\Entity\Organization\User;
use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PaymentVoter extends Voter
{
    use VoterTrait;

    /** @var User */
    protected $user;

    /** @var Payment */
    protected $payment;

    /** @var SupportGroup */
    protected $supportGroup;

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Support\Payment;
    }

    protected function voteOnAttribute(string $attribute, $payment, TokenInterface $token): bool
    {
        $this->user = $token->getUser();
        $this->payment = $payment;
        $this->supportGroup = $this->payment->getSupportGroup();

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
        if (($this->payment->getCreatedBy() && $this->payment->getCreatedBy()->getId() === $this->user->getId())
            || ($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->user->getId())
            || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->user->getId())
        ) {
            return true;
        }

        return false;
    }
}
