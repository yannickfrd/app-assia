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
                $em->getEventManager()->removeEventListener(['onFlush'], $listener);
            }
        }
    }

    public function disableListener(EntityManagerInterface $em, string $listenerClassName): void
    {
        foreach ($em->getEventManager()->getListeners() as $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof $listenerClassName) {
                    $em->getEventManager()->removeEventListener(['onFlush'], $listener);
                    break;
                }
            }
        }
    }

    public function disableFilter(EntityManagerInterface $em, string $filter): void
    {
        if ($em->getFilters()->isEnabled($filter)) {
            $em->getFilters()->disable($filter);
        }
    }

    public function enableFilter(EntityManagerInterface $em, string $filter): void
    {
        if (!$em->getFilters()->isEnabled($filter)) {
            $em->getFilters()->enable($filter);
        }
    }
}
