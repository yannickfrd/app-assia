<?php

namespace App\EventListener;

use Twig\Environment;
use App\Notification\MailNotification;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    private $security;
    private $renderer;
    private $notification;
    private $adminEmail;
    private $exceptionListener;

    public function __construct(Security $security, Environment $renderer, MailNotification $notification, string $adminEmail, bool $exceptionListener)
    {
        $this->security = $security;
        $this->renderer = $renderer;
        $this->notification = $notification;
        $this->adminEmail = $adminEmail;
        $this->exceptionListener = $exceptionListener;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        if (!$this->security->getUser() || !$this->exceptionListener) {
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
}
