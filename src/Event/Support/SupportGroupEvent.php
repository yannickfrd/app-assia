<?php

namespace App\Event\Support;

use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

class SupportGroupEvent extends Event
{
    public const NAME = 'support.event';

    private $supportGroup;
    private $form;
    private $currentReferent;

    public function __construct(SupportGroup $supportGroup, ?FormInterface $form = null, ?User $currentReferent = null)
    {
        $this->supportGroup = $supportGroup;
        $this->form = $form;
        $this->currentReferent = $currentReferent;
    }

    public function getSupportGroup(): SupportGroup
    {
        return $this->supportGroup;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }

    public function getCurrentReferent(): ?User
    {
        return $this->currentReferent;
    }
}
