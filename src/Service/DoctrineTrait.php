<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

trait DoctrineTrait
{
    public function disableListeners(EntityManagerInterface $manager)
    {
        $listenersType = $manager->getEventManager()->getListeners();
        foreach ($listenersType as $listenerType) {
            foreach ($listenerType as $listener) {
                $manager->getEventManager()->removeEventListener(['onFlush', 'onFlush'], $listener);
            }
        }
    }

    public function disableFilter(EntityManagerInterface $manager, $filter)
    {
        $manager->getFilters()->disable($filter);
    }
}
