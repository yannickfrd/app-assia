<?php

namespace App\Event\Support;

use App\Entity\Support\SupportGroup;
use Symfony\Contracts\EventDispatcher\Event;

class SupportGroupEvent extends Event
{
    public const NAME = 'support_group.event';

    private $supportGroup;

    public function __construct(SupportGroup $supportGroup)
    {
        $this->supportGroup = $supportGroup;
    }

    public function getSupportGroup(): SupportGroup
    {
        return $this->supportGroup;
    }
}
