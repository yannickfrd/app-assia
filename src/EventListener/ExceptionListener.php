<?php

namespace App\EventListener;

use Twig\Environment;
use App\Notification\MailNotification;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    private $renderer;
    private $adminEmail;

    public function __construct(Environment $renderer, MailNotification $notification, $adminEmail)
    {
        $this->renderer = $renderer;
        $this->notification = $notification;
        $this->adminEmail = $adminEmail;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        $message = sprintf(
            'Exception throwed : %s with code : %s',
            $exception->getMessage(),
            $exception->getCode()
        );

        $htmlBody = $this->renderer->render(
            'emails/exception.html.twig',
            ['exception' => $exception]
        );

        $this->notification->send([
            'email' => $this->adminEmail,
            'name' => 'Adminitrateur',
        ], 'Esperer95.app : '.$message, $htmlBody);
    }
}
