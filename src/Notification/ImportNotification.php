<?php

namespace App\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ImportNotification extends MailNotifier
{
    public function sendNotif(string $content)
    {
        $email = (new TemplatedEmail())
            ->to($this->getAdminEmail())
            ->subject('Esperer95.app | Doublons personnes')
            ->html($content);

        $this->mailer->send($email);
    }
}
