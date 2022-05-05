<?php

namespace App\Notification;

use App\Entity\Organization\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class UserNotification extends MailNotifier
{
    /**
     * Mail d'initialisation du mot de passe.
     */
    public function newUser(User $user): bool
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Application Assia'.('prod' != $this->appVersion ? ' version DEMO' : null).' : Création de compte | '.$user->getFullname())
            ->htmlTemplate('emails/email_new_user.html.twig')
            ->context([
                'user' => $user,
                'app_version' => $this->appVersion,
            ]);

        return $this->send($email);
    }

    /**
     * Mail de réinitialisation du mot de psasse.
     */
    public function reinitPassword(User $user)
    {
        $email = (new TemplatedEmail())
            ->to($user->getEmail())
            ->subject('Application Assia : Réinitialisation du mot de passe | '.$user->getFullname())
            ->htmlTemplate('emails/email_reinit_password.html.twig')
            ->context(['user' => $user]);

        if ($this->send($email)) {
            return [
                'type' => 'success',
                'content' => "Un mail vous a été envoyé. Le lien est valide durant 5 minutes. <br/>
                    Si vous n'avez rien reçu, veuillez vérifier dans les courriers indésirables.",
            ];
        }

        return [
            'type' => 'danger',
            'content' => "Une erreur s'est produite. L'email n'a pas pu être envoyé.",
        ];
    }
}
