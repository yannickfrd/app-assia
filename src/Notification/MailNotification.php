<?php

namespace App\Notification;

use App\Entity\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Twig\Environment;

class MailNotification
{
    protected $renderer;
    protected $appVersion;
    protected $host;
    protected $username;
    protected $password;
    protected $port;

    public function __construct(Environment $renderer, $appVersion = 'prod',
        $host = 'localhost', $username = null, $password = null, $port = 25)
    {
        $this->renderer = $renderer;
        $this->appVersion = $appVersion;
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
    }

    public function send(array $to, string $subject, string $htmlBody, string $txtBody = null)
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP(); // Send using SMTP

        if (isset($_SERVER['SERVER_NAME']) && $_SERVER['SERVER_NAME'] != '127.0.0.1:8000') {
            $mail->SMTPAuth = true; // Enable SMTP authentication
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
        } else {
            $mail->SMTPAuth = false;
            $mail->SMTPAutoTLS = false;
        }

        $mail->Host = $this->host;
        $mail->Username = $this->username;
        $mail->Password = $this->password;
        $mail->Port = $this->port;

        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true); // Set email format to HTML

        try {
            $mail->setFrom('noreply@romain-mad.fr', 'Esperer95.app');
            $mail->addAddress($to['email'], $to['name']); // Add a recipient
            // $mail->addAddress("ellen@example.com"); // Name is optional
            // $mail->addReplyTo("info@example.com", "Information");
            // $mail->addCC("cc@example.com");
            // $mail->addBCC("bcc@example.com");

            // Attachments
            // $mail->addAttachment("/var/tmp/file.tar.gz"); // Add attachments
            // $mail->addAttachment("/tmp/image.jpg", "new.jpg"); // Optional name

            // Content
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $txtBody ?? null;

            $mail->send();

            return true;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            // dd($e);

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
     * Mail d'initialisation du mot de psasse.
     */
    public function createUserAccount(User $user)
    {
        $to = [
            'email' => $user->getEmail(),
            'name' => $user->getFullname(),
        ];

        $subject = 'Esperer95.app'.($this->appVersion == 'test' ? ' version TEST' : null).' : Création de compte | '.$user->getFullname();

        $context = [
            'user' => $user,
            'app_version' => $this->appVersion,
        ];

        $htmlBody = $this->renderer->render(
            'emails/createUserAccountEmail.html.twig',
            $context,
        );
        $txtBody = $this->renderer->render(
            'emails/createUserAccountEmail.txt.twig',
            $context,
        );

        $this->send($to, $subject, $htmlBody, $txtBody);
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
