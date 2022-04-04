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

    public function disableFilter(EntityManagerInterface $em, string $filter): void
    {
        if ($this->_em->getFilters()->isEnabled($filter)) {
            $em->getFilters()->disable($filter);
        }
    }
}
