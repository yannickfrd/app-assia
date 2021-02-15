<?php

namespace App\EventSubscriber;

use App\Notification\ExceptionNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    // private $exceptionNotification;
    // private $exceptionListener;

    // public function __construct(ExceptionNotification $exceptionNotification, bool $exceptionListener)
    // {
    //     $this->exceptionNotification = $exceptionNotification;
    //     $this->exceptionListener = $exceptionListener;
    // }

    public static function getSubscribedEvents()
    {
        // return the subscribed events, their methods and priorities
        return [
            KernelEvents::EXCEPTION => [
                // ['processException', 10],
                ['logException', 0],
                ['notifyException', -10],
            ],
            // ExportDataEvent::NAME => [
            //     ['onExport', 0],
            // ],
        ];
    }

    // public function onExport(ExportDataEvent $event)
    // {
    //     // $this->testService->onDataExport($event->getSupportSearch(), $event->getRepo());
    // }

    public function processException(ExceptionEvent $event)
    {
        if (!$this->exceptionListener) {
            return;
        }

        $this->exceptionNotification->sendException($event->getThrowable());
    }

    public function logException(ExceptionEvent $event)
    {
        // ...
    }

    public function notifyException(ExceptionEvent $event)
    {
        // ...
    }
}
