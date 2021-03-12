<?php

namespace App\EventDispatcher\Support;

use App\EntityManager\SupportDuplicator;
use App\Event\Support\SupportGroupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SupportGroupDuplicatorSubscriber implements EventSubscriberInterface
{
    private $supportDuplicator;
    private $flashBag;

    public function __construct(SupportDuplicator $supportDuplicator, FlashBagInterface $flashBag)
    {
        $this->supportDuplicator = $supportDuplicator;
        $this->flashBag = $flashBag;
    }

    public static function getSubscribedEvents()
    {
        return [
            'support_group.duplicator' => 'duplicateSupportGroup',
        ];
    }

    public function duplicateSupportGroup(SupportGroupEvent $event)
    {
        $supportGroup = $event->getSupportGroup();

        if ($this->supportDuplicator->duplicate($supportGroup)) {
                                    
            return $this->flashBag->add('success', 'Les informations du précédent suivi ont été ajoutées (évaluation sociale, documents...)');
        } 
        
        return $this->flashBag->add('warning', 'Aucun autre suivi n\'a été trouvé.');
    }
}
