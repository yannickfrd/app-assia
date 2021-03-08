<?php

namespace App\EventDispatcher\Support;

use App\EntityManager\SupportDuplicator;
use App\Event\Support\SupportGroupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SupportGroupDuplicatorSubscriber implements EventSubscriberInterface
{
    private $supportDuplicator;

    public function __construct(SupportDuplicator $supportDuplicator)
    {
        $this->supportDuplicator = $supportDuplicator;
    }

    public static function getSubscribedEvents()
    {
        return [
            'support_group.duplicator' => 'duplicateSupportGroup',
        ];
    }

    public function duplicateSupportGroup(SupportGroupEvent $event)
    {
        $this->supportDuplicator->duplicate($event->getSupportGroup());
    }
}
