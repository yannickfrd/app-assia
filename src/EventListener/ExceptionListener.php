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

    public function onKernelException(ExceptionEvent $event): ?bool
    {
        $exception = $event->getThrowable();

        if (!$this->security->getUser() || !$this->exceptionListener
            || $exception->getPrevious() && in_array($exception->getPrevious()->getCode(), [403, 404])) {
            return null;
        }

        return $this->exceptionNotification->sendException($exception);
    }
}
