<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

trait DoctrineTrait
{
    public function disableListeners(EntityManagerInterface $em): void
    {
        $listenersType = $em->getEventManager()->getListeners();
        foreach ($listenersType as $listenerType) {
            foreach ($listenerType as $listener) {
                $em->getEventManager()->removeEventListener(['onFlush', 'onFlush'], $listener);
            }
        }
    }

    public function disableFilter(EntityManagerInterface $em, $filter): void
    {
        $em->getFilters()->disable($filter);
    }
}
