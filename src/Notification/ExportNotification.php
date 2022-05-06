<?php

namespace App\Notification;

use App\Entity\Admin\Export;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class ExportNotification extends MailNotifier
{
    public function sendExport(string $to, Export $export)
    {
        $email = (new TemplatedEmail())
            ->to($to)
            ->subject('Application Assia | Export de données')
            ->htmlTemplate('emails/email_export_file.html.twig')
            ->context(['export' => $export]);

        $this->mailer->send($email);
    }
}
