<?php

namespace App\Event\Rdv;

use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\EventDispatcher\Event;

class RdvEvent extends Event
{
    public const NAME = 'rdv.event';

    private $rdv;
    private $supportGroup;
    private $form;

    public function __construct(Rdv $rdv, SupportGroup $supportGroup = null, FormInterface $form = null)
    {
        $this->rdv = $rdv;
        $this->supportGroup = $supportGroup;
        $this->form = $form;
    }

    public function getRdv(): Rdv
    {
        return $this->rdv;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup ?? $this->rdv->getSupportGroup();
    }

    public function getForm(): FormInterface
    {
        return $this->form;
    }
}
