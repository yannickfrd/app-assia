<?php

declare(strict_types=1);

namespace App\Controller\Security;

use App\Entity\Organization\User;
use App\Form\Admin\Security\ChangePasswordType;
use App\Form\Admin\Security\ForgotPasswordType;
use App\Form\Admin\Security\InitPasswordType;
use App\Form\Admin\Security\LoginType;
use App\Form\Admin\Security\ReinitPasswordType;
use App\Form\Admin\Security\SecurityUserDevicesType;
use App\Form\Admin\Security\SecurityUserServicesType;
use App\Form\Admin\Security\SecurityUserType;
use App\Form\Model\Security\UserChangeInfo;
use App\Form\Model\Security\UserChangePassword;
use App\Form\Model\Security\UserInitPassword;
use App\Form\Model\Security\UserResetPassword;
use App\Form\Organization\User\UserChangeInfoType;
use App\Form\Organization\User\UserSettingType;
use App\Notification\UserNotification;
use App\Repository\Organization\ServiceUserRepository;
use App\Repository\Organization\UserRepository;
use App\Service\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * @Route("/admin/registration", name="security_registration", methods="GET|POST")
     */
    public function registration(Request $request, UserNotification $userNotification): Response
    {
        $form = $this->createForm(SecurityUserType::class, $user = new User())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->createUser($user, $userNotification);

            return $this->redirectToRoute('security_user', ['id' => $user->getId()]);
        }

        return $this->renderForm('app/admin/security/security_user.html.twig', ['form' => $form]);
    }

    /**
     * Renvoie un email à l'utilisateur afin qu'il puisse (re)définir son mot de passe.
     *
     * @Route("/admin/user/{id}/send_new_email", name="security_user_send_new_email", methods="GET")
     */
    public function sendNewEmailToUser(User $user, UserNotification $userNotification): Response
    {
        $this->userManager->generateNewToken($user, $userNotification);

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

        $form = $this->createForm(LoginType::class, ['username' => $authenticationUtils->getLastUsername()]);

        return $this->render('app/admin/security/login.html.twig', [
            'form' => $form->createView(),
            'lastusername' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Création du mot de passe par l'utilisateur à sa première connexion.
     *
     * @Route("/login/init_password", name="security_init_password", methods="GET|POST")
     */
    public function initPassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(InitPasswordType::class, $userInitPassword = new UserInitPassword())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updatePassword($this->getUser(), $passwordHasher, $userInitPassword->getPassword());

            return $this->redirectToRoute('home');
        }

        return $this->render('app/admin/security/security_init_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Fiche de l'utilisateur connecté.
     *
     * @Route("/my_profile", name="my_profile", methods="GET|POST")
     */
    public function showCurrentUser(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $setting = $user->getSetting() ?? $this->userManager->getUserSetting($user);
        $formSetting = $this->createForm(UserSettingType::class, $setting)
            ->handleRequest($request);

        $userChangeInfo = (new UserChangeInfo())
            ->setEmail($user->getEmail())
            ->setPhone1($user->getPhone1())
            ->setPhone2($user->getPhone2())
        ;

        $form = $this->createForm(UserChangeInfoType::class, $userChangeInfo, ['user' => $user])
            ->handleRequest($request);

        $userChangePassword = new UserChangePassword();

        $formPassword = $this->createForm(ChangePasswordType::class, $userChangePassword)
            ->handleRequest($request);

        if ($formSetting->isSubmitted() && $formSetting->isValid()) {
            $this->userManager->updateSetting($user->setSetting($setting));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updateCurrentUserInfo($user, $userChangeInfo);
        }

        if ($formPassword->isSubmitted() && $formPassword->isValid()) {
            $this->userManager->updatePassword($user, $passwordHasher, $userChangePassword->getNewPassword());

            return $this->redirectToRoute('home');
        }
        if ($formPassword->isSubmitted() && !$formPassword->isValid()) {
            $this->addFlash('danger ', 'security.invalid_password');
        }

        return $this->render('app/organization/user/user.html.twig', [
            'form_user' => $form->createView(),
            'form_password' => $formPassword->createView(),
            'form_setting' => $formSetting->createView(),
        ]);
    }

    /**
     * @Route("/admin/user/{id}", name="security_user", methods="GET|POST")
     */
    public function editUser(User $user, Request $request, EntityManagerInterface $em,
        ServiceUserRepository $serviceUserRepo): Response
    {
        $form = $this->createForm(SecurityUserType::class, $user)
            ->handleRequest($request);

        $formUserDevices = $this->createForm(SecurityUserDevicesType::class, $user)
            ->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid())
            || ($formUserDevices->isSubmitted() && $formUserDevices->isValid())) {
            $em->flush();

            $this->userManager->deleteCacheItems($user);

            $this->addFlash('success ', 'security.user.updated_successfully');
        }

        return $this->renderForm('app/admin/security/security_user.html.twig', [
            'form' => $form,
            'form_user_devices' => $formUserDevices,
            'form_user_services' => $this->createForm(SecurityUserServicesType::class),
            'user_services' => $serviceUserRepo->findUserServices($user),
        ]);
    }

    /**
     * Désactive ou réactive l'utilisateur.
     *
     * @Route("/admin/user/{id}/disable", name="security_user_disable", methods="GET")
     */
    public function disableUser(User $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $user);

        if ($user === $this->getUser()) {
            $this->addFlash('danger', 'security.user.disabled_not_allowed');

            return $this->redirectToRoute('security_user', ['id' => $user->getId()]);
        }

        if ($user->getDisabledAt()) {
            $user->setDisabledAt(null);
            $this->addFlash('success', 'security.user.actived_successfully');
        } else {
            $user->setPassword('')
                ->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'security.user.disabled_successfully');
        }

        $this->userManager->deleteCacheItems($user);

        $em->flush();

        return $this->redirectToRoute('security_user', ['id' => $user->getId()]);
    }

    /**
     * Page dans le cas d'un mot de passe oublié.
     *
     * @Route("/login/forgot_password", name="security_forgot_password", methods="GET|POST")
     */
    public function forgotPassword(Request $request, UserRepository $userRepo, UserNotification $userNotification): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(ForgotPasswordType::class, $userResetPassword = new UserResetPassword())
            ->handleRequest($request);

        if ($form->isSubmitted()) {
            // Vérifie si l'utilisateur existe
            $user = $this->userManager->userExists($userRepo, $userResetPassword);

            if ($user) {
                $message = $this->userManager->sendEmailToReinitPassword($user, $userNotification);

                if ($message) {
                    return $this->redirectToRoute('security_login');
                }
            }
            $this->addFlash('danger', 'security.invalid_login_or_email');
        }

        return $this->render('app/admin/security/security_forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Réinitialise le mot de passe de l'utilisateur.
     *
     * @Route("/login/reinit_password/{token}", name="security_reinit_password", methods="GET|POST")
     */
    public function reinitPassword(Request $request, UserRepository $userRepo, UserPasswordHasherInterface $passwordHasher, string $token = null): Response
    {
        $form = $this->createForm(ReinitPasswordType::class, $userResetPassword = new UserResetPassword())
            ->handleRequest($request);

        // Vérifie si l'utilisateur existe avec le même token.
        $user = $this->userManager->userWithTokenExists($userRepo, $userResetPassword, $token);
        if ($form->isSubmitted()) {
            if ($user) {
                if ($form->isValid()) {
                    $this->userManager->updatePasswordWithToken($user, $passwordHasher, $userResetPassword);

                    return $this->redirectToRoute('security_login');
                }
            } else {
                $this->addFlash('danger', 'security.invalid_login_or_email');
            }
        }

        return $this->render('app/admin/security/security_reinit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Création du mot de passe de l'utilisateur.
     *
     * @Route("/login/create_password/{token}", name="security_create_password", methods="GET|POST")
     */
    public function createPassword(Request $request, UserRepository $userRepo, UserPasswordHasherInterface $passwordHasher, string $token = null): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if ($this->getUser()) {
            return $this->redirectToRoute('security_logout');
        }

        $form = $this->createForm(ReinitPasswordType::class, $userResetPassword = new UserResetPassword())
            ->handleRequest($request);

        // Vérifie si le token existe en base de données.
        if (0 === $userRepo->count(['token' => $token])) {
            $this->addFlash('danger', 'security.invalid_link');

            return $this->redirectToRoute('security_login');
        }

        if ($form->isSubmitted()) {
            // Vérifie si l'utilisateur existe avec le même token.
            $user = $this->userManager->userWithTokenExists($userRepo, $userResetPassword, $token);
            if ($form->isValid() && $user) {
                $this->userManager->createPasswordWithToken($user, $passwordHasher, $userResetPassword);

                return $this->redirectToRoute('security_login');
            } else {
                $this->addFlash('danger', 'security.invalid_login_or_email');
            }
        }

        return $this->render('app/admin/security/security_reinit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout(): void
    {
    }
}
