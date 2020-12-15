<?php

namespace App\Command;

trait DoctrineTrait
{
    protected $manager;

    public function disableListeners()
    {
        $listenersType = $this->manager->getEventManager()->getListeners();
        foreach ($listenersType as $listenerType) {
            foreach ($listenerType as $listener) {
                $this->manager->getEventManager()->removeEventListener(['onFlush', 'onFlush'], $listener);
            }
        }
    }
}
