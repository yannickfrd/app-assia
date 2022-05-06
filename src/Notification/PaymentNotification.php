<?php

namespace App\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

class PaymentNotification extends MailNotifier
{
    public function sendPayment(array $to, string $subject, array $context, string $path)
    {
        $emailUser = $this->getUser()->getEmail();

        $email = (new TemplatedEmail())
            ->to(...$to)
            ->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->htmlTemplate('emails/email_receipt_payment.html.twig')
            ->context($context)
            ->bcc($emailUser)
            ->replyTo($emailUser)
            ->attachFromPath($path);

        $this->mailer->send($email);
    }
}
