<?php

namespace App\Notification;

use App\Entity\Organization\User;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Symfony\Component\HttpFoundation\Request;
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

    public function send(array $to, string $subject, string $htmlBody, string $txtBody = null, string $cc = null, string $bcc = null, string $replyTo = null, array $attachments = []): bool
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP(); // Send using SMTP

        $request = Request::createFromGlobals();

        if ('127.0.0.1:8000' != $request->server->get('SERVER_NAME')) {
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
            if ($cc) { // Copie
                $mail->addCC($cc);
            }
            if ($bcc) { // Copie cachée
                $mail->addBCC($bcc);
            }
            if ($replyTo) { // Copie cachée
                $mail->addReplyTo($replyTo, 'Information');
            }

            // Attachments
            foreach ($attachments as $path) {
                $mail->addAttachment($path);
            }

            // Content
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $txtBody ?? null;

            return $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";

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
     * Mail d'initialisation du mot de passe.
     */
    public function createUserAccount(User $user): bool
    {
        $to = [
            'email' => $user->getEmail(),
            'name' => $user->getFullname(),
        ];

        $subject = 'Esperer95.app'.('test' == $this->appVersion ? ' version TEST' : null).' : Création de compte | '.$user->getFullname();

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

        return $this->send($to, $subject, $htmlBody, $txtBody);
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
                'content' => "Un mail vous a été envoyé. Le lien est valide durant 5 minutes. <br/>Si vous n'avez rien reçu, veuillez vérifier dans les courriers indésirables.",
            ];
        }

        return [
            'type' => 'danger',
            'content' => "Une erreur s'est produite. L'email n'a pas pu être envoyé.",
        ];
    }
}
