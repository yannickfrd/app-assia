<?php

namespace App\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Email;

class ContributionNotification extends MailNotifier
{
    public function sendContribution(array $to, string $subject, array $context, string $path)
    {
        $emailUser = $this->getUser()->getEmail();

        $email = (new TemplatedEmail())
            ->to(...$to)
            ->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->htmlTemplate('emails/receiptPayment.html.twig')
            ->context($context)
            ->bcc($emailUser)
            ->replyTo($emailUser)
            ->attachFromPath($path);

        $this->mailer->send($email);
    }
}
