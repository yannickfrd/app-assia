<?php

namespace App\Notification;

use App\Entity\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Twig\Environment;

class MailNotification
{
    protected $mail;
    protected $renderer;

    public function __construct(Environment $renderer, $host = 'localhost', $username = null, $password = null, $port = 25)
    {
        $this->mail = new PHPMailer(true);

        $this->mail->isSMTP(); // Send using SMTP

        if ($_SERVER['SERVER_NAME'] != '127.0.0.1:8000') {
            $this->mail->SMTPAuth = true; // Enable SMTP authentication
            $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        } else {
            $this->mail->SMTPAuth = false;
            $this->mail->SMTPAutoTLS = false;
        }

        $this->mail->Host = $host;
        $this->mail->Username = $username;
        $this->mail->Password = $password;
        $this->mail->Port = $port;

        $this->mail->CharSet = 'UTF-8';
        $this->mail->isHTML(true); // Set email format to HTML

        $this->renderer = $renderer;
    }

    public function send($to, $subject, $htmlBody, $txtBody = null)
    {
        try {
            //Recipients
            $this->mail->setFrom('noreply@romain-mad.fr', 'Esperer95.app');
            $this->mail->addAddress($to['email'], $to['name']); // Add a recipient
            // $this->mail->addAddress("ellen@example.com"); // Name is optional
            // $this->mail->addReplyTo("info@example.com", "Information");
            // $this->mail->addCC("cc@example.com");
            // $this->mail->addBCC("bcc@example.com");

            // Attachments
            // $this->mail->addAttachment("/var/tmp/file.tar.gz"); // Add attachments
            // $this->mail->addAttachment("/tmp/image.jpg", "new.jpg"); // Optional name

            // Content
            $this->mail->Subject = $subject;
            $this->mail->Body = $htmlBody;
            $this->mail->AltBody = $txtBody ?? null;

            $this->mail->send();

            // echo "Message has been sent";
            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
            dd($e);

            return false;
        }
    }

    public function altSend($to, $subject, $htmlBody)
    {
        $headers = [
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html;charset=UTF-8',
            'From' => 'Esperer95.app <noreply@esperer95-app.fr>',
            // "CC" => $cc,
            // "Bcc" => $bcc,
            // "Reply-To" => "Esperer95.app <romain.madelaine@esperer-95.org>",
            'X-Mailer' => 'PHP/'.phpversion(),
        ];
        mail($to, $subject, $htmlBody, $headers);
    }

    /**
     * Mail de réinitialisation du mot de psasse.
     */
    public function reinitPassword(User $user)
    {
        $to = [
            'email' => $user->getEmail(),
            'name' => $user->getFullname(),
        ];

        $subject = 'Esperer95.app : Réinitialisation du mot de passe';

        $htmlBody = $this->renderer->render(
            'emails/reinitPasswordEmail.html.twig',
            ['user' => $user]
        );
        $txtBody = $this->renderer->render(
            'emails/reinitPasswordEmail.txt.twig',
            ['user' => $user]
        );

        $send = $this->send($to, $subject, $htmlBody, $txtBody);

        if ($send) {
            return [
                'type' => 'success',
                'content' => "Un mail vous a été envoyé. Si vous n'avez rien reçu, merci de vérifier dans vos courriers indésirables.",
            ];
        }

        return [
            'type' => 'danger',
            'content' => "Une erreur s'est produite. L'email n'a pas pu être envoyé.",
        ];
    }
}
