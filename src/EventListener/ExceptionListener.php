<?php

namespace App\EventListener;

use App\Notification\ExceptionNotification;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Security;

class ExceptionListener
{
    private $security;
    private $exceptionNotification;
    private $exceptionListener;

    public function __construct(Security $security, ExceptionNotification $exceptionNotification, bool $exceptionListener)
    {
        $this->security = $security;
        $this->exceptionNotification = $exceptionNotification;
        $this->exceptionListener = $exceptionListener;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (!$this->security->getUser() || !$this->exceptionListener
            || $exception->getPrevious() && 403 === $exception->getPrevious()->getCode()) {
            return;
        }

        $this->exceptionNotification->sendException($exception);
    }
}
