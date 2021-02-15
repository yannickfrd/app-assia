<?php

namespace App\Notification;

use App\Entity\Organization\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class MailNotification
{
    protected $mailer;

    public function __construct(MailerInterface $mailer, $appVersion = 'prod')
    {
        $this->mailer = $mailer;
        $this->appVersion = $appVersion;
    }

    public function send(
        string $to,
        string $subject,
        string $htmlTemplate,
        array $context = [],
        string $textTemplate = null,
        string $cc = null,
        string $bcc = null,
        string $replyTo = null,
        string $priority = Email::PRIORITY_NORMAL,
        array $attachments = []
    ): bool {
        $email = (new TemplatedEmail())
            ->to($to)
            ->priority($priority)
            ->subject($subject)
            ->htmlTemplate($htmlTemplate)
            ->textTemplate($textTemplate)
            ->context($context);

        if ($cc) {
            $email->cc($cc);
        }
        if ($bcc) {
            $email->bcc($bcc);
        }
        if ($replyTo) {
            $email->replyTo($replyTo);
        }
 
        foreach ($attachments as $path) {
            $email->attachFromPath($path);
        }

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            throw $e;

            return false;
        }

        return true;
    }

    /**
     * Mail d'initialisation du mot de passe.
     */
    public function createUserAccount(User $user): bool
    {
        return $this->send(
            $user->getEmail(),
            'Esperer95.app'.('prod' != $this->appVersion ? ' version DEMO' : null).' : Création de compte | '.$user->getFullname(),
            'emails/createUserAccountEmail.html.twig',
            [
                'user' => $user,
                'app_version' => $this->appVersion,
            ]
        );
    }

    /**
     * Mail de réinitialisation du mot de psasse.
     */
    public function reinitPassword(User $user)
    {
        $send = $this->send(
            $user->getEmail(),
            'Esperer95.app : Réinitialisation du mot de passe',
            'emails/reinitPasswordEmail.html.twig',
            ['user' => $user]
        );

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
