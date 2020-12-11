<?php

namespace App\EntityManager;

use App\Entity\User;
use App\Entity\Service;
use App\Form\Model\UserChangeInfo;
use App\Repository\UserRepository;
use App\Form\Model\UserResetPassword;
use App\Notification\MailNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserManager
{
    private $manager;
    private $flashbag;

    public function __construct(EntityManagerInterface $manager, FlashBagInterface $flashbag)
    {
        $this->manager = $manager;
        $this->flashbag = $flashbag;
    }

    /**
     * Crée un utilisateur.
     */
    public function createUser(User $user, MailNotification $notification): void
    {
        $user->setToken(bin2hex(random_bytes(32)))
            ->setTokenCreatedAt(new \DateTime());

        $this->manager->persist($user);
        $this->manager->flush();

        $this->discache($user);

        $notification->createUserAccount($user);

        $this->addFlash('success', 'Le compte de '.$user->getFirstname().' est créé. Un e-mail lui a été envoyé.');
    }

    /**
     * Génère un nouveau token à l'utilisateur et lui envoie un email.
     */
    public function generateNewToken(User $user, MailNotification $notification): void
    {
        $user->setToken(bin2hex(random_bytes(32)))
            ->setTokenCreatedAt(new \DateTime());

        $this->manager->flush();

        if ($notification->createUserAccount($user)) {
            $this->addFlash('success', 'Un e-mail a été envoyé à l\'utilisateur. Le lien est valide durant 24 heures.');
        } else {
            $this->addFlash('danger', 'L\'email n\'a pu être envoyé.');
        }
    }

    /**
     * Met à jour le mot de passe.
     */
    public function updatePassword(User $user, UserPasswordEncoderInterface $encoder, string $password): void
    {
        $hashPassword = $encoder->encodePassword($user, $password);
        $user->setPassword($hashPassword);

        $this->manager->flush();

        $this->addFlash('success', 'Votre mot de passe est mis à jour !');
    }

    /**
     * Met à jour les coordonnées de l'utilisateur connecté.
     */
    public function updateCurrentUserInfo(User $user, UserChangeInfo $userChangeInfo)
    {
        $user->setEmail($userChangeInfo->getEmail())
            ->setPhone1($userChangeInfo->getPhone1())
            ->setPhone2($userChangeInfo->getPhone2());

        $this->manager->flush();

        $this->addFlash('success', 'Les modifications sont enregistrées.');
    }

    /**
     * Vérifie si l'utilisateur existe.
     */
    public function userExists(UserRepository $repoUser, UserResetPassword $userResetPassword): ?User
    {
        return $repoUser->findOneBy([
            'username' => $userResetPassword->getUsername(),
            'email' => $userResetPassword->getEmail(),
        ]);
    }

    /**
     * Vérifie si l'utilisateur avvec un token existe.
     */
    public function userWithTokenExists(UserRepository $repoUser, UserResetPassword $userResetPassword, string $token = null): ?User
    {
        return $repoUser->findOneBy([
            'username' => $userResetPassword->getUsername(),
            'email' => $userResetPassword->getEmail(),
            'token' => $token,
        ]);
    }

    /**
     * Crée le mot de passe si le token est toujours valide.
     */
    public function createPasswordWithToken(User $user, UserPasswordEncoderInterface $encoder, UserResetPassword $userResetPassword)
    {
        if ($this->isValidTokenDate($user, 24 * 60 * 60)) { // 24 heures
            $this->setPassword($user, $encoder, $userResetPassword->getPassword());

            return $this->addFlash('success', 'Votre mot de passe est créé !');
        }

        return $this->addFlash('danger', 'Le lien de création est périmé.');
    }

    /**
     * Met à jour le mot de passe si le token est toujours valide.
     */
    public function updatePasswordWithToken(User $user, UserPasswordEncoderInterface $encoder, UserResetPassword $userResetPassword)
    {
        if ($this->isValidTokenDate($user, 5 * 60)) { // 5 minutes
            // Met à jour le nouveau mot de passe et supprime le token
            $this->setPassword($user, $encoder, $userResetPassword->getPassword());

            $user->setFailureLoginCount(0);

            $this->manager->flush();

            return $this->addFlash('success', 'Votre mot de passe est réinitialisé !');
        }

        return $this->addFlash('danger', 'Le lien de réinitialisation est périmé.');
    }

    /**
     * Envoie l'email de réinitialisation du mot de passe.
     */
    public function sendEmailToReinitPassword(User $user, MailNotification $notification)
    {
        $user->setToken(bin2hex(random_bytes(32))) // Enregistre le token dans la base
            ->setTokenCreatedAt(new \DateTime());

        $this->manager->flush();

        $message = $notification->reinitPassword($user); // Envoie l'email

        $this->addFlash($message['type'], $message['content']);

        return $message;
    }

    protected function setPassword(User $user, UserPasswordEncoderInterface $encoder, string $plainPassword): void
    {
        $user->setToken(null)
            ->setTokenCreatedAt(null)
            ->setPassword($encoder->encodePassword($user, $plainPassword));

        $this->manager->flush();
    }

    /**
     * Vérifie si le token n'est pas périmé par rapport à sa date.
     */
    protected function isValidTokenDate(User $user, int $delay)
    {
        // Calcule l'intervalle entre le moment de demande de réinitialisation et maintenant
        $interval = date_timestamp_get(new \DateTime()) - date_timestamp_get($user->getTokenCreatedAt());

        return $interval < $delay;
    }

    /**
     * Supprime les utilisateurs en cache pour chaque service.
     */
    public function discache(User $user): bool
    {
        $cache = new FilesystemAdapter();

        foreach ($user->getServices() as $service) {
            $cache->deleteItem(Service::CACHE_SERVICE_USERS_KEY.$service->getId());
        }

        return $cache->deleteItem(User::CACHE_USER_SERVICES_KEY.$user->getId());
    }

    /**
     * Ajoute un message flash.
     */
    protected function addFlash(string $alert, string $msg): void
    {
        $this->flashbag->add($alert, $msg);
    }
}
