<?php

namespace App\Controller\Admin;

use App\Entity\Organization\User;
use App\EntityManager\UserManager;
use App\Form\Admin\Security\ChangePasswordType;
use App\Form\Admin\Security\ForgotPasswordType;
use App\Form\Admin\Security\InitPasswordType;
use App\Form\Admin\Security\LoginType;
use App\Form\Admin\Security\ReinitPasswordType;
use App\Form\Admin\Security\SecurityUserType;
use App\Form\Model\Security\UserChangeInfo;
use App\Form\Model\Security\UserChangePassword;
use App\Form\Model\Security\UserInitPassword;
use App\Form\Model\Security\UserResetPassword;
use App\Form\Organization\User\UserChangeInfoType;
use App\Notification\MailNotification;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\UserRepository;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/admin/registration", name="security_registration", methods="GET|POST")
     */
    public function registration(Request $request, UserPasswordEncoderInterface $encoder, MailNotification $notification): Response
    {
        $user = new User();
        $user->setPassword($encoder->encodePassword($user, bin2hex(random_bytes(8))));

        $form = ($this->createForm(SecurityUserType::class, $user))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user->getServiceUser()->count() > 0) {
                $this->userManager->createUser($user, $notification);

                return $this->redirectToRoute('security_user', ['id' => $user->getId()]);
            }
            $this->addFlash('danger', "Veuillez rattacher l'utilisateur au minimum à un service.");
        }

        return $this->render('app/admin/security/securityUser.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Renvoie un email à l'utilisateur afin qu'il puisse (re)définir son mot de passe.
     *
     * @Route("/admin/user/{id}/send_new_email", name="security_user_send_new_email", methods="GET")
     */
    public function sendNewEmailToUser(User $user, MailNotification $notification): Response
    {
        $this->userManager->generateNewToken($user, $notification);

        return $this->redirectToRoute('security_user', ['id' => $user->getId()]);
    }

    /**
     * Page de connexion.
     *
     * @Route("/login", name="security_login", priority=1, methods="GET|POST")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(LoginType::class, ['_username' => $authenticationUtils->getLastUsername()]);

        return $this->render('app/admin/security/login.html.twig', [
            'form' => $form->createView(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    // /**
    //  * Création du mot de passe par l'utilisateur à sa première connexion.
    //  *
    //  * @Route("/login/after_login", name="security_after_login", methods="GET|POST")
    //  */
    // public function afterLogin(): Response
    // {
    //     $this->addFlash('success', 'Bonjour '.$this->getUser()->getFirstname().' !');

    //     if (1 == $this->getUser()->getLoginCount() && $this->getUser()->getTokenCreatedAt()) {
    //         return $this->redirectToRoute('security_init_password');
    //     }

    //     return $this->redirectToRoute('home');
    // }

    /**
     * Création du mot de passe par l'utilisateur à sa première connexion.
     *
     * @Route("/login/init_password", name="security_init_password", methods="GET|POST")
     */
    public function initPassword(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $userInitPassword = new UserInitPassword();

        $form = ($this->createForm(InitPasswordType::class, $userInitPassword))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updatePassword($this->getUser(), $encoder, $userInitPassword->getPassword());

            return $this->redirectToRoute('home');
        }

        return $this->render('app/admin/security/initPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fiche de l'utilisateur connecté.
     *
     * @Route("/my_profile", name="my_profile", methods="GET|POST")
     */
    public function showCurrentUser(Request $request, UserPasswordEncoderInterface $encoder, SupportGroupRepository $repoSupport, ServiceRepository $repoService): Response
    {
        $userChangeInfo = (new UserChangeInfo())
            ->setEmail($this->getUser()->getEmail())
            ->setPhone1($this->getUser()->getPhone1())
            ->setPhone2($this->getUser()->getPhone2());

        $form = ($this->createForm(UserChangeInfoType::class, $userChangeInfo))
            ->handleRequest($request);

        $userChangePassword = new UserChangePassword();

        $formPassword = $this->createForm(ChangePasswordType::class, $userChangePassword);
        $formPassword->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updateCurrentUserInfo($this->getUser(), $userChangeInfo);
        }

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $this->userManager->updatePassword($this->getUser(), $encoder, $userChangePassword->getNewPassword());

            return $this->redirectToRoute('home');
        }
        if ($formPassword->isSubmitted() && !$formPassword->isValid()) {
            $this->addFlash('danger ', 'Le mot de passe ou la confirmation sont invalides.');
        }

        return $this->render('app/organization/user/user.html.twig', [
            'form' => $form->createView(),
            'formPassword' => $formPassword->createView(),
            'supports' => $repoSupport->findSupportsOfUser($this->getUser()),
            'services' => $repoService->findServicesOfUser($this->getUser()),
        ]);
    }

    /**
     * @Route("/admin/user/{id}", name="security_user", methods="GET|POST")
     */
    public function editUser(User $user, Request $request, EntityManagerInterface $manager)
    {
        $form = ($this->createForm(SecurityUserType::class, $user))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->userManager->discache($user);

            $this->addFlash('success', 'Le compte de '.$user->getFirstname().' est mis à jour.');
        }

        return $this->render('app/admin/security/securityUser.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Désactive ou réactive l'utilisateur.
     *
     * @Route("/admin/user/{id}/disable", name="security_user_disable", methods="GET")
     */
    public function disableUser(User $user, EntityManagerInterface $manager): Response
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

        $this->userManager->discache($user);

        $manager->flush();

        return $this->redirectToRoute('security_user', ['id' => $user->getId()]);
    }

    /**
     * Page dans le cas d'un mot de passe oublié.
     *
     * @Route("/login/forgot_password", name="security_forgot_password", methods="GET|POST")
     */
    public function forgotPassword(Request $request, UserRepository $repoUser, MailNotification $notification): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $userResetPassword = new UserResetPassword();

        $form = ($this->createForm(ForgotPasswordType::class, $userResetPassword))
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifie si l'utilisateur existe
            $user = $this->userManager->userExists($repoUser, $userResetPassword);

            if ($user) {
                $message = $this->userManager->sendEmailToReinitPassword($user, $notification);

                if ($message) {
                    return $this->redirectToRoute('security_login');
                }
            }
            $this->addFlash('danger', "Le login ou l'adresse email sont incorrects.");
        }

        return $this->render('app/admin/security/forgotPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Réinitialise le mot de passe de l'utilisateur.
     *
     * @Route("/login/reinit_password/{token}", name="security_reinit_password", methods="GET|POST")
     */
    public function reinitPassword(Request $request, UserRepository $repoUser, UserPasswordEncoderInterface $encoder, string $token = null): Response
    {
        $userResetPassword = new UserResetPassword();

        $form = ($this->createForm(ReinitPasswordType::class, $userResetPassword))
            ->handleRequest($request);

        // Vérifie si l'utilisateur existe avec le même token.
        $user = $this->userManager->userWithTokenExists($repoUser, $userResetPassword, $token);
        if ($form->isSubmitted()) {
            if ($user) {
                if ($form->isValid()) {
                    $this->userManager->updatePasswordWithToken($user, $encoder, $userResetPassword);

                    return $this->redirectToRoute('security_login');
                }
            } else {
                $this->addFlash('danger', "Le login ou l'adresse email sont incorrects.");
            }
        }

        return $this->render('app/admin/security/reinitPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Création du mot de passe de l'utilisateur.
     *
     * @Route("/login/create_password/{token}", name="security_create_password", methods="GET|POST")
     */
    public function createPassword(Request $request, UserRepository $repoUser, UserPasswordEncoderInterface $encoder, string $token = null): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('security_logout');
        }

        $userResetPassword = new UserResetPassword();

        $form = ($this->createForm(ReinitPasswordType::class, $userResetPassword))
            ->handleRequest($request);

        // Vérifie si le token existe en base de données.
        if (0 === $repoUser->count(['token' => $token])) {
            $this->addFlash('danger', 'Le lien est expiré ou invalide.');

            return $this->redirectToRoute('security_login');
        }

        if ($form->isSubmitted()) {
            // Vérifie si l'utilisateur existe avec le même token.
            $user = $this->userManager->userWithTokenExists($repoUser, $userResetPassword, $token);
            if ($form->isValid() && $user) {
                $this->userManager->createPasswordWithToken($user, $encoder, $userResetPassword);

                return $this->redirectToRoute('security_login');
            } else {
                $this->addFlash('danger', "Le login ou l'adresse email sont incorrects.");
            }
        }

        return $this->render('app/admin/security/reinitPassword.html.twig', [
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
}
