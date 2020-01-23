<?php

namespace App\Notification;

use App\Entity\User;
use Twig\Environment;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class MailNotification
{
    protected $mail;
    protected $renderer;

    public function __construct(Environment $renderer)
    {
        $this->mail = new PHPMailer(true);

        $this->mail->SMTPAuth = true; // Enable SMTP authentication
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        if (strchr($_SERVER["HTTP_HOST"], "127.0.0.1")) {
            $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
            $this->mail->Host = "localhost"; // Set the SMTP server to send through
            $this->mail->Username = ""; // SMTP username
            $this->mail->Password = ""; // SMTP password
            $this->mail->Port = 25; // TCP port to connect to        
        } else {
            $this->mail->isSMTP(); // Send using SMTP
            $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
            $this->mail->Host = "smtp.ionos.fr";
            $this->mail->Username = "esperer95-app@romain-mad.fr";
            $this->mail->Password = "Esp3r3r-95*";
            $this->mail->Port = 25;
        }

        $this->mail->CharSet = "UTF-8";
        $this->mail->isHTML(true); // Set email format to HTML

        $this->renderer = $renderer;
    }

    public function send($to, $subject, $htmlBody, $txtBody = null)
    {
        try {
            //Recipients
            $this->mail->setFrom("noreply@romain-mad.fr", "Esperer95-app");
            $this->mail->addAddress($to["email"], $to["name"]);     // Add a recipient
            // $this->mail->addAddress("ellen@example.com");               // Name is optional
            // $this->mail->addReplyTo("info@example.com", "Information");
            // $this->mail->addCC("cc@example.com");
            // $this->mail->addBCC("bcc@example.com");

            // Attachments
            // $this->mail->addAttachment("/var/tmp/file.tar.gz");         // Add attachments
            // $this->mail->addAttachment("/tmp/image.jpg", "new.jpg");    // Optional name

            // Content
            $this->mail->Subject = $subject;
            $this->mail->Body    = $htmlBody;
            $this->mail->AltBody = $txtBody ?? null;

            $this->mail->send();

            // echo "Message has been sent";
            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
            // return false;
            dd($e);
        }
    }

    public function altSend($to, $subject, $htmlBody)
    {
        $headers = [
            "MIME-Version" => "1.0",
            "Content-type" => "text/html;charset=UTF-8",
            "From" => "Esperer95-app <noreply@esperer95-app.fr>",
            // "CC" => $cc,
            // "Bcc" => $bcc,
            // "Reply-To" => "Esperer95-app <romain.madelaine@esperer-95.org>",
            "X-Mailer" => "PHP/" . phpversion()
        ];
        mail($to, $subject, $htmlBody, $headers);
    }

    /**
     * Mail de réinitialisation du mot de psasse
     *
     * @param User $user
     */
    public function reinitPassword(User $user)
    {
        $to = [
            "email" => $user->getEmail(),
            "name" =>  $user->getFullname()
        ];

        $subject = "Esperer95-app : Réinitialisation du mot de passe";

        $htmlBody = $this->renderer->render(
            "emails/reinitPassword.html.twig",
            ["user" => $user]
        );
        $txtBody = $this->renderer->render(
            "emails/reinitPassword.txt.twig",
            ["user" => $user]
        );

        $send = $this->send($to, $subject, $htmlBody, $txtBody);

        if ($send) {
            return [
                "type" => "success",
                "content" => "Un mail vous a été envoyé. Si vous n'avez rien reçu, merci de vérifier dans vos courriers indésirables."
            ];
        }
        return [
            "type" => "danger",
            "content" => "Une erreur s'est produite. L'email n'a pas pu être envoyé."
        ];
    }
}
