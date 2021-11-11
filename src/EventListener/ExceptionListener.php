<?php

namespace App\EventListener;

use App\Notification\ExceptionNotification;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Security;

class ExceptionListener
{
    private const IGNORED_CODE = [403, 404];

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
        $code = $exception instanceof HttpException ? $exception->getStatusCode() : 500;

        if ($this->security->getUser() && $this->exceptionListener && $exception && !in_array($code, self::IGNORED_CODE)) {
            return $this->exceptionNotification->sendException($exception, $code);
        }

        return null;
    }
}
