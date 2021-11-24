<?php

namespace App\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ExceptionNotification extends MailNotifier
{
    public function sendException(\Throwable $exception, ?int $code = null): bool
    {
        $subject = sprintf(
            'Application Assia | Exception throwed : %s with code : %s',
            $exception->getMessage(),
            $code ?? $exception->getCode(),
        );

        $email = (new TemplatedEmail())
            ->to($this->getAdminEmail())
            ->subject($subject)
            ->htmlTemplate('emails/exceptionEmail.html.twig')
            ->context([
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'trace' => $exception->getTraceAsString(),
            ])
        ;

        return $this->send($email);
    }
}
