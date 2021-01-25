<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;

trait DoctrineTrait
{
    /**
     * @var EntityManagerInterface
     */
    protected $manager;

    public function disableListeners()
    {
        $listenersType = $this->manager->getEventManager()->getListeners();
        foreach ($listenersType as $listenerType) {
            foreach ($listenerType as $listener) {
                $this->manager->getEventManager()->removeEventListener(['onFlush', 'onFlush'], $listener);
            }
        }
        $this->manager->getFilters()->disable('softdeleteable');
    }
}
