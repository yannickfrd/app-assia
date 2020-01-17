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

    public function reinitPassword(User $user)
    {
        $message = (new \Swift_Message())
            ->setSubject("Esperer95-app : Réinitialisation du mot de passe")
            ->setFrom(["romain.madelaine@gmail.com" => "Esperer95-app"])
            ->setTo([$user->getEmail() => $user->getFullname()]);

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

    public function reinitPassword2(User $user)
    {
        $to = $user->getEmail();
        $subject = "Esperer95-app : Réinitialisation du mot de passe";
        $message = $this->renderer->render(
            "emails/reinitPassword.html.twig",
            ["user" => $user]
        );

        $headers = [
            "MIME-Version" => "1.0",
            "Content-type" => "text/html;charset=UTF-8",
            "From" => "Esperer95-app <contact@romain-mad.fr>",
            // "CC" => $cc,
            // "Bcc" => $bcc,
            "Reply-To" => "Esperer95-app <romain.madelaine@esperer-95.org>",
            "X-Mailer" => "PHP/" . phpversion()
        ];

        mail($to, $subject, $message, $headers);
    }
}
