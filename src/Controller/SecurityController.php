<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\User;
use App\Form\Model\UserChangeInfo;
use App\Form\Model\UserChangePassword;
use App\Form\Model\UserInitPassword;
use App\Form\Model\UserResetPassword;
use App\Form\Security\ChangePasswordType;
use App\Form\Security\ForgotPasswordType;
use App\Form\Security\InitPasswordType;
use App\Form\Security\ReinitPasswordType;
use App\Form\Security\SecurityUserEditType;
use App\Form\Security\SecurityUserType;
use App\Form\User\UserChangeInfoType;
use App\Notification\MailNotification;
use App\Repository\ServiceRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $manager;
    private $encoder;
    private $repo;

    public function __construct(EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, UserRepository $repo)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
        $this->repo = $repo;
    }

    /**
     * @Route("/admin/registration", name="security_registration", methods="GET|POST")
     */
    public function registration(Request $request): Response
    {
        $user = new User();

        $form = ($this->createForm(SecurityUserType::class, $user))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (count($user->getServiceUser()) > 0) {
                return $this->createUser($user);
            }
            $this->addFlash('danger', "Veuillez rattacher l'utilisateur au minimum à un service.");
        }

        return $this->render('app/security/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="security_login", methods="GET|POST")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirect('home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = $this->repo->findOneBy(['username' => $lastUsername]);

        if ($error) {
            $this->errorLogin($user ?? null);
        }

        return $this->render('app/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Création du mot de passe par l'utilisateur à sa première connexion.
     *
     * @Route("/login/after_login", name="security_after_login", methods="GET|POST")
     */
    public function afterLogin(): Response
    {
        $this->addFlash('success', 'Bonjour '.$this->getUser()->getFirstname().' !');

        if (1 == $this->getUser()->getLoginCount() && $this->getUser()->getTokenCreatedAt()) {
            return $this->redirectToRoute('security_init_password');
        }

        return $this->redirectToRoute('home');
    }

    /**
     * Création du mot de passe par l'utilisateur à sa première connexion.
     *
     * @Route("/login/init_password", name="security_init_password", methods="GET|POST")
     */
    public function initPassword(Request $request, UserInitPassword $userInitPassword = null): Response
    {
        $userInitPassword = new UserInitPassword();

        $form = ($this->createForm(InitPasswordType::class, $userInitPassword))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updatePassword($userInitPassword->getPassword());
        }

        return $this->render('app/security/initPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fiche de l'utilisateur connecté.
     *
     * @Route("/my_profile", name="my_profile", methods="GET|POST")
     */
    public function showCurrentUser(UserChangeInfo $userChangeInfo = null, UserChangePassword $userChangePassword = null, SupportGroupRepository $repoSupport, ServiceRepository $repoService, Request $request): Response
    {
        $userChangeInfo->setEmail($this->getUser()->getEmail())
            ->setPhone1($this->getUser()->getPhone1())
            ->setPhone2($this->getUser()->getPhone2());

        $form = ($this->createForm(UserChangeInfoType::class, $userChangeInfo))
            ->handleRequest($request);

        $userChangePassword = new UserChangePassword();

        $formPassword = $this->createForm(ChangePasswordType::class, $userChangePassword);
        $formPassword->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateInfoCurrentUser($userChangeInfo);
        }

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            return $this->updatePassword($userChangePassword->getNewPassword());
        }
        if ($formPassword->isSubmitted() && !$formPassword->isValid()) {
            $this->addFlash('danger ', 'Le mot de passe ou la confirmation sont invalides.');
        }

        return $this->render('app/user/user.html.twig', [
            'form' => $form->createView(),
            'formPassword' => $formPassword->createView(),
            'supports' => $repoSupport->findAllSupportsFromUser($this->getUser()),
            'services' => $repoService->findAllServicesFromUser($this->getUser()),
        ]);
    }

    /**
     * @Route("/admin/user/{id}", name="security_user", methods="GET|POST")
     */
    public function editUser(User $user, Request $request)
    {
        $form = ($this->createForm(SecurityUserEditType::class, $user))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->discache($user);

            $this->addFlash('success', 'Le compte de '.$user->getFirstname().' est mis à jour.');
        }

        return $this->render('app/security/securityUser.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Désactive ou réactive l'utilisateur.
     *
     * @Route("/admin/user/{id}/disable", name="security_user_disable", methods="GET")
     */
    public function disableUser(User $user): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $user);

        if ($user == $this->getUser()) {
            $this->addFlash('danger', 'Vous ne pouvez pas vous-même désactiver votre compte utilisateur.');

            return $this->redirectToRoute('security_user', ['id' => $user->getId()]);
        }

        if ($user->getDisabledAt()) {
            $user->setDisabledAt(null);
            $this->addFlash('success', 'Ce compte utilisateur est ré-activé.');
        } else {
            $user->setUsername('xxx')
                ->setPassword('xxx')
                ->setRoles([])
                ->setEmail('xxx')
                ->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Ce compte utilisateur est désactivé.');
        }

        $this->discache($user);

        $this->manager->flush();

        return $this->redirectToRoute('security_user', ['id' => $user->getId()]);
    }

    /**
     * Page dans le cas d'un mot de passe oublié.
     *
     * @Route("/login/forgot_password", name="security_forgot_password", methods="GET|POST")
     */
    public function forgotPassword(Request $request, UserResetPassword $userResetPassword = null, MailNotification $notification): Response
    {
        $userResetPassword = new UserResetPassword();

        $form = ($this->createForm(ForgotPasswordType::class, $userResetPassword))
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifie si l'utilisateur existe
            $user = $this->userExists($userResetPassword);
            if ($user) {
                return $this->sendEmailReinitPassword($user, $notification);
            }
            $this->addFlash('danger', "Le login ou l'adresse email sont incorrects.");
        }

        return $this->render('app/security/forgotPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Réinitialise le mot de passe de l'utilisateur.
     *
     * @Route("/login/reinit_password", name="security_reinit_password", methods="GET|POST")
     */
    public function reinitPassword(Request $request): Response
    {
        $userResetPassword = new UserResetPassword();

        $form = ($this->createForm(ReinitPasswordType::class, $userResetPassword))
            ->handleRequest($request);

        // Vérifie si l'utilisateur existe avec le même token.
        $user = $this->userWithTokenExists($userResetPassword, $request->get('token'));
        if ($form->isSubmitted()) {
            if ($user) {
                if ($form->isValid()) {
                    return $this->updatePasswordWithToken($user, $userResetPassword);
                }
            } else {
                $this->addFlash('danger', "Le login ou l'adresse email sont incorrects.");
            }
        }

        return $this->render('app/security/reinitPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Réinitialise le mot de passe de l'utilisateur.
     *
     * @Route("/login/create_password", name="security_create_password", methods="GET|POST")
     */
    public function createPassword(Request $request): Response
    {
        $userResetPassword = new UserResetPassword();

        $form = ($this->createForm(ReinitPasswordType::class, $userResetPassword))
            ->handleRequest($request);

        // Vérifie si le token existe en base de données.
        if (!$this->repo->findOneBy(['token' => $request->get('token')])) {
            $this->addFlash('danger', 'Le lien est expiré ou invalide.');

            return $this->redirectToRoute('security_login');
        }

        // Vérifie si l'utilisateur existe avec le même token.
        $user = $this->userWithTokenExists($userResetPassword, $request->get('token'));
        if ($form->isSubmitted()) {
            if ($user) {
                if ($form->isValid()) {
                    return $this->updatePasswordWithToken($user, $userResetPassword);
                }
            } else {
                $this->addFlash('danger', "Le login ou l'adresse email sont incorrects.");
            }
        }

        return $this->render('app/security/reinitPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/connexion", name="security_login_valid")
     */
    public function loginValid(AuthenticationUtils $authenticationUtils)
    {
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout(): void
    {
    }

    /**
     * Crée un utilisateur.
     */
    protected function createUser(User $user): Response
    {
        $hashPassword = $this->encoder->encodePassword($user, $user->getPassword());

        $user->setPassword($hashPassword)
            ->setLoginCount(0);

        $this->manager->persist($user);
        $this->manager->flush();

        $this->discache($user);

        $this->addFlash('success', 'Le compte de '.$user->getFirstname().' est créé.');

        return $this->redirectToRoute('security_user', [
            'id' => $user->getId(),
        ]);
    }

    /**
     * En cas d'erreur lors de la tentative de connexion.
     */
    protected function errorLogin(User $user = null): void
    {
        if ($user) {
            $user->setFailureLoginCount($user->getFailureLoginCount() + 1);

            $this->manager->flush();
            if ($user->getFailureLoginCount() >= 5) {
                $this->addFlash('danger', "Ce compte est bloqué suite à de nombreux échecs de connexion.<br/> 
                Veuillez-vous rapprocher d'un administrateur ou réinitialiser votre mot de passe.");
            }
        }

        $this->addFlash('danger', 'Identifiant ou mot de passe incorrect.');

        return;
    }

    /**
     * Met à jour les coordonnées de l'utilisateur connecté.
     */
    protected function updateInfoCurrentUser(UserChangeInfo $userChangeInfo)
    {
        $this->getUser()->setEmail($userChangeInfo->getEmail())
            ->setPhone1($userChangeInfo->getPhone1())
            ->setPhone2($userChangeInfo->getPhone2());

        $this->manager->flush();

        $this->addFlash('success', 'Les modifications sont enregistrées.');
    }

    /**
     * Met à jour le mot de passe.
     */
    protected function updatePassword(string $password): Response
    {
        $hashPassword = $this->encoder->encodePassword($this->getUser(), $password);
        $this->getUser()->setPassword($hashPassword);

        $this->manager->flush();

        $this->addFlash('success', 'Votre mot de passe est mis à jour !');

        return $this->redirectToRoute('home');
    }

    /**
     * Vérifie si l'utilisateur existe.
     */
    protected function userExists(UserResetPassword $userResetPassword): ?User
    {
        return $this->repo->findOneBy([
            'username' => $userResetPassword->getUsername(),
            'email' => $userResetPassword->getEmail(),
        ]);
    }

    /**
     * Vérifie si l'utilisateur avvec un token existe.
     */
    protected function userWithTokenExists(UserResetPassword $userResetPassword, string $token = null): ?User
    {
        return $this->repo->findOneBy([
            'username' => $userResetPassword->getUsername(),
            'email' => $userResetPassword->getEmail(),
            'token' => $token,
        ]);
    }

    /**
     * Met à jour le mot de passe avec un token.
     */
    protected function updatePasswordWithToken(User $user, userResetPassword $userResetPassword)
    {
        $interval = date_timestamp_get(new \DateTime()) - date_timestamp_get($user->getTokenCreatedAt() ?? new \DateTime()); // Calcule l'intervalle entre le moment de demande de réinitialisation et maintenant
        $delay = $user->getLastLogin() ? (5 * 60) : (7 * 24 * 60 * 60); // 5 minutes x 60 secondes
        // Si le lien de réinitialisaiton est toujours valide
        if ($interval < $delay) {
            $hashPassword = $this->encoder->encodePassword($user, $userResetPassword->getPassword());
            // Met à jour le nouveau mot de passe
            $user->setPassword($hashPassword)
                ->setToken(null);

            $this->manager->flush();

            $this->addFlash('success', 'Votre mot de passe est '.($user->getLastLogin() ? 'réinitialisé' : 'créé').' !');

            return $this->redirectToRoute('security_login');
        }
        $this->addFlash('danger', 'Le lien de '.($user->getLastLogin() ? 'réinitialisation' : 'création').' est périmé.');

        return $this->redirectToRoute('security_reinit_password');
    }

    /**
     * Envoie l'email de réinitialisation.
     */
    protected function sendEmailReinitPassword(User $user, MailNotification $notification)
    {
        $user->setToken(bin2hex(random_bytes(32))) // Enregistre le token dans la base
            ->setTokenCreatedAt(new \DateTime());

        $this->manager->flush();

        $message = $notification->reinitPassword($user); // Envoie l'email

        $this->addFlash($message['type'], $message['content']);

        if ($message) {
            return $this->redirectToRoute('security_login');
        }

        return;
    }

    /**
     * Supprime les utilisateurs en cache pour chaque service.
     */
    protected function discache(User $user): bool
    {
        $cache = new FilesystemAdapter();

        foreach ($user->getServices() as $service) {
            $cache->deleteItem(Service::CACHE_SERVICE_USERS_KEY.$service->getId());
        }

        return $cache->deleteItem(User::CACHE_USER_SERVICES_KEY.$user->getId());
    }
}
