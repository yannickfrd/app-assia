<?php

namespace App\Notification;

use App\Entity\User;
use Swift_Mailer;
use Swift_Attachment;
use Swift_Image;
use Twig\Environment;

class MailNotification
{
    private $mailer;
    private $renderer;

    public function __construct(Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }

    public function sendMail($name, $title, $from, $to, $replyTo = null, $emailHtml, $emailTxt = null)
    {
        $message = (new \Swift_Message())
            ->setSubject($title)
            ->setFrom($from)
            ->setTo($to)
            // ->setReplyTo($replyTo)
            ->setBody(
                $this->renderer->render(
                    $emailHtml,
                    ["name" => $name]
                ),
                "text/html"
            )
            ->addPart(
                $this->renderer->render(
                    $emailTxt,
                    ["name" => $name]
                ),
                "text/plain"
            );
        // $message->attach(Swift_Attachment::fromPath($path));

        $this->mailer->send($message);
    }


    public function reinitPassword(User $user)
    {
        //Solid (single responsability)


        $message = (new \Swift_Message())
            ->setSubject("Esperer95-app : Réinitialisation du mot de passe")
            ->setFrom(["noreply@esperer-95.org" => "Esperer95-app"])
            ->setTo(["romain.madelaine@esperer-95.org" => $user->getUsername()]);

        $message->setBody(
            $this->renderer->render(
                "emails/reinitPassword.html.twig",
                ["user" => $user]
            ),
            "text/html"
        )
            ->addPart(
                $this->renderer->render(
                    "emails/reinitPassword.txt.twig",
                    ["user" => $user]
                ),
                "text/plain"
            );

        $this->mailer->send($message);
    }
}
