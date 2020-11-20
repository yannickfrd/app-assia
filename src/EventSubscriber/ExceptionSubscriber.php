<?php

namespace App\EventSubscriber;

use App\Event\ExportDataEvent;
use App\Notification\MailNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class ExceptionSubscriber implements EventSubscriberInterface
{
    private $renderer;
    private $notification;
    private $adminEmail;
    private $exceptionListener;

    // public function __construct(Environment $renderer, MailNotification $notification, string $adminEmail, bool $exceptionListener)
    // {
    //     $this->renderer = $renderer;
    //     $this->notification = $notification;
    //     $this->adminEmail = $adminEmail;
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

        $exception = $event->getThrowable();
        $statusCodeMethod = 'getStatusCode';

        $message = sprintf(
            'Exception throwed : %s with code : %s',
            $exception->getMessage(),
            method_exists($exception, $statusCodeMethod) ? $exception->$statusCodeMethod() : $exception->getCode(),
        );

        $htmlBody = $this->renderer->render(
            'emails/exceptionEmail.html.twig',
            ['exception' => $exception]
        );

        $this->notification->send(
            ['email' => $this->adminEmail, 'name' => 'Adminitrateur'],
            'Esperer95.app : '.$message,
            $htmlBody
        );
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
