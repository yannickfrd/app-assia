<?php

namespace App\Service\User;

use App\Entity\Admin\Setting;
use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceSetting;
use App\Entity\Organization\User;
use App\Entity\Organization\UserSetting;
use App\Form\Model\Security\UserChangeInfo;
use App\Form\Model\Security\UserResetPassword;
use App\Notification\UserNotification;
use App\Repository\Organization\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserManager
{
    private $em;
    private $flashBag;
    private $translator;

    public function __construct(
        EntityManagerInterface $em,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        /** @var Session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
        $this->translator = $translator;
    }

    /**
     * Crée un utilisateur.
     */
    public function createUser(User $user, UserNotification $userNotification): void
    {
        $user->setToken(bin2hex(random_bytes(32)))
            ->setTokenCreatedAt(new \DateTime())
            ->setSetting($user->getSetting() ?? $this->getUserSetting($user));

        $this->em->persist($user);
        $this->em->flush();

        $this->deleteCacheItems($user);

        $userNotification->newUser($user);

        $this->flashBag->add('success', $this->translator->trans('user.created_successfully', [
            'user_firstname' => $user->getFirstname(),
        ], 'app'));
    }

    public function getUserSetting(User $user): UserSetting
    {
        // If the user has no service, we get the application's config.
        if (!$user->getServices() || !$user->getServices()->first() || !$user->getServices()->first()->getSetting()) {
            return $this->hydrateUserSetting($this->em->getRepository(Setting::class)->findOneBy([]));
        }

        return $this->hydrateUserSetting($user->getServices()->first()->getSetting());
    }

    /**
     * @param Setting|ServiceSetting $setting
     */
    private function hydrateUserSetting($setting): ?UserSetting
    {
        return (new UserSetting())
            ->setDailyAlert($setting ? $setting->getDailyAlert() : false)
            ->setWeeklyAlert($setting ? $setting->getWeeklyAlert() : false);
    }

    /**
     * Génère un nouveau token à l'utilisateur et lui envoie un email.
     */
    public function generateNewToken(User $user, UserNotification $userNotification): void
    {
        $user->setToken(bin2hex(random_bytes(32)))
            ->setTokenCreatedAt(new \DateTime());

        $this->em->flush();

        if ($userNotification->newUser($user)) {
            $this->flashBag->add('success', 'user.email_sent_successfully');
        } else {
            $this->flashBag->add('danger', 'error.email_not_sent');
        }
    }

    /**
     * Met à jour le mot de passe.
     */
    public function updatePassword(User $user, UserPasswordHasherInterface $passwordHasher, string $password): void
    {
        $hashPassword = $passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashPassword);

        $this->em->flush();

        $this->flashBag->add('success', 'current_user.password.updated_successfully');
    }

    /**
     * Met à jour les coordonnées de l'utilisateur connecté.
     */
    public function updateCurrentUserInfo(User $user, UserChangeInfo $userChangeInfo): void
    {
        $user->setEmail($userChangeInfo->getEmail())
            ->setPhone1($userChangeInfo->getPhone1())
            ->setPhone2($userChangeInfo->getPhone2());

        $this->em->flush();

        $this->flashBag->add('success', 'updated_successfully');
    }

    public function updateSetting(): void
    {
        $this->em->flush();

        $this->flashBag->add('success', 'settings.updated_successfully');
    }

    /**
     * Vérifie si l'utilisateur existe.
     */
    public function userExists(UserRepository $userRepo, UserResetPassword $userResetPassword): ?User
    {
        return $userRepo->findOneBy([
            'username' => $userResetPassword->getUsername(),
            'email' => $userResetPassword->getEmail(),
        ]);
    }

    /**
     * Vérifie si l'utilisateur avvec un token existe.
     */
    public function userWithTokenExists(UserRepository $userRepo, UserResetPassword $userResetPassword, string $token = null): ?User
    {
        return $userRepo->findOneBy([
            'username' => $userResetPassword->getUsername(),
            'email' => $userResetPassword->getEmail(),
            'token' => $token,
        ]);
    }

    /**
     * Crée le mot de passe si le token est toujours valide.
     */
    public function createPasswordWithToken(User $user, UserPasswordHasherInterface $passwordHasher, UserResetPassword $userResetPassword): void
    {
        if ($this->isValidTokenDate($user, 24 * 60 * 60)) { // 24 heures
            $this->setPassword($user, $passwordHasher, $userResetPassword->getPassword());

            $this->flashBag->add('success', 'current_user.password.created_successfully');
        } else {
            $this->flashBag->add('danger', 'security.invalid_link');
        }
    }

    /**
     * Met à jour le mot de passe si le token est toujours valide.
     */
    public function updatePasswordWithToken(User $user, UserPasswordHasherInterface $passwordHasher, UserResetPassword $userResetPassword): void
    {
        if ($this->isValidTokenDate($user, 10 * 60)) { // 10 minutes
            // Met à jour le nouveau mot de passe et supprime le token
            $this->setPassword($user, $passwordHasher, $userResetPassword->getPassword());

            $user->setFailureLoginCount(0);

            $this->em->flush();

            $this->flashBag->add('success', 'current_user.password.updated_successfully');
        } else {
            $this->flashBag->add('danger', 'security.invalid_link');
        }
    }

    /**
     * Envoie l'email de réinitialisation du mot de passe.
     */
    public function sendEmailToReinitPassword(User $user, UserNotification $userNotification): array
    {
        $user->setToken(bin2hex(random_bytes(32))) // Enregistre le token dans la base
            ->setTokenCreatedAt(new \DateTime());

        $this->em->flush();

        $message = $userNotification->reinitPassword($user); // Envoie l'email

        $this->flashBag->add($message['type'], $message['content']);

        return $message;
    }

    protected function setPassword(User $user, UserPasswordHasherInterface $passwordHasher, string $plainPassword): void
    {
        $user->setToken(null)
            ->setTokenCreatedAt(null)
            ->setPassword($passwordHasher->hashPassword($user, $plainPassword));

        $this->em->flush();
    }

    /**
     * Vérifie si le token n'est pas périmé par rapport à sa date.
     */
    protected function isValidTokenDate(User $user, int $delay): bool
    {
        // Calcule l'intervalle entre le moment de demande de réinitialisation et maintenant
        $interval = date_timestamp_get(new \DateTime()) - date_timestamp_get($user->getTokenCreatedAt());

        return $interval < $delay;
    }

    /**
     * Supprime les utilisateurs en cache pour chaque service.
     */
    public static function deleteCacheItems(User $user): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        foreach ($user->getServices() as $service) {
            $cache->deleteItem(Service::CACHE_SERVICE_USERS_KEY.$service->getId());
        }

        return $cache->deleteItem(User::CACHE_USER_SERVICES_KEY.$user->getId());
    }
}
