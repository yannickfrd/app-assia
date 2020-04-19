<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        // Liste des évènements, méthodes et priorités
        return [
           'foo' => [
               ['doSomething', 10],
               ['doOtherThing', 0],
           ],
           'bar' => [
               ['doBarThing', -10],
            ],
        ];
    }

    public function doSomething($event)
    {
    }
}
