<?php

namespace App\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ExceptionNotification extends MailNotifier
{
    public function sendException(\Throwable $exception): bool
    {
        $statusCodeMethod = 'getStatusCode';

        $message = sprintf(
            'Exception throwed : %s with code : %s',
            $exception->getMessage(),
            method_exists($exception, $statusCodeMethod) ? $exception->$statusCodeMethod() : $exception->getCode(),
        );

        $email = (new TemplatedEmail())
            ->to($this->getAdminEmail())
            ->subject('Esperer95.app : '.$message)
            ->htmlTemplate('emails/exceptionEmail.html.twig')
            ->context(['exception' => $exception]);

        return $this->send($email);
    }
}
