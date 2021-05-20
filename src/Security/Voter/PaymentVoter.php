<?php

namespace App\Security\Voter;

use App\Entity\Support\Payment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PaymentVoter extends Voter
{
    use VoterTrait;

    protected $user;
    protected $userId;
    protected $payment;

    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['VIEW', 'EDIT', 'DELETE'])
            && $subject instanceof \App\Entity\Support\Payment;
    }

    protected function voteOnAttribute($attribute, $payment, TokenInterface $token): bool
    {
        /** @var User */
        $this->user = $token->getUser();
        $this->userId = $this->user->getId();
        /** @var Payment */
        $this->payment = $payment;
        $this->supportGroup = $this->payment->getSupportGroup();

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
        return $this->canView();
    }

    protected function canDelete(): bool
    {
        return $this->canView();
    }

    protected function isCreatorOrReferent(): bool
    {
        if (($this->payment->getCreatedBy() && $this->payment->getCreatedBy()->getId() === $this->userId)
            || ($this->supportGroup->getReferent() && $this->supportGroup->getReferent()->getId() === $this->userId)
            || ($this->supportGroup->getReferent2() && $this->supportGroup->getReferent2()->getId() === $this->userId)
        ) {
            return true;
        }

        return false;
    }
}
