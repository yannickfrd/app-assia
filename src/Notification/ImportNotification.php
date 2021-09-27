<?php

namespace App\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ImportNotification extends MailNotifier
{
    public function sendNotif(string $content)
    {
        $email = (new TemplatedEmail())
            ->to($this->getAdminEmail())
            ->subject('Application Assia | Doublons personnes')
            ->html($content);

        $this->mailer->send($email);
    }
}
