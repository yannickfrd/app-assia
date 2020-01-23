<?php

namespace App\Notification;

use Swift_Mailer;
use Swift_Attachment;
use Swift_Image;

class SwiftMailerNotification
{
    private $mailer;
    private $renderer;

    public function __construct(Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function send($to, $subject, $htmlBody, $txtBody = null)
    {
        $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom(["noreply@esperer95-app.fr" => "Esperer95-app"])
            ->setTo($to["email"], $to["name"] = null);

        $message->setBody($htmlBody, "text/html")
            ->addPart($txtBody, "text/plain");

        $this->mailer->send($message);
    }
}
