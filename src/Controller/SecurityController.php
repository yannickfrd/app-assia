<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\UserChangeInfo;
use App\Repository\UserRepository;
use App\Form\Model\UserInitPassword;

use App\Form\Model\UserResetPassword;

use App\Form\User\UserChangeInfoType;

use App\Form\Model\UserChangePassword;
use App\Notification\MailNotification;
use App\Form\Security\InitPasswordType;
use App\Form\Security\RegistrationType;
use App\Form\Security\SecurityUserType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Security\ChangePasswordType;
use App\Form\Security\ForgotPasswordType;
use App\Form\Security\ReinitPasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    private $manager;
    private $encoder;
    private $repo;

    public function __construct(EntityManagerInterface $manager, Security $security, UserPasswordEncoderInterface $encoder, UserRepository $repo)
    {
        $this->manager = $manager;
        $this->user = $security->getUser();
        $this->encoder = $encoder;
        $this->repo = $repo;
    }

    /**
     * @Route("/admin/registration", name="security_registration") 
     */
    public function registration(Request $request)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hashPassword = $this->encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hashPassword)
                ->setLoginCount(0)
                ->setActive(true)
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->user);

            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash("success", "Le compte a été créé.");

            return $this->redirectToRoute("security_user", [
                "id" => $user->getId(),
            ]);
        }

        return $this->render("security/registration.html.twig", [
            "form" => $form->createView(),
        ]);
    }

    /**
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Redirection vers la page d'accueil si l'utilisateur est déjà connecté
        if ($this->user) {
            return $this->redirect("home");
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = $this->repo->findOneBy(["username" => $lastUsername]);

        if ($user && $user->getFailureLoginCount() >= 10) {
            $this->addFlash("danger", "Ce compte a été bloqué suite à de nombreux échecs de connexion.");
            return $this->redirectToRoute("security_logout");
        }

        if ($error) {
            $this->errorLogin($user);
        }

        return $this->render("security/login.html.twig", [
            "last_username" => $lastUsername,
            "error" => $error
        ]);
    }

    /**
     * En cas d'erreur lors de la tentative de connexion
     * 
     * @param User $user
     * @return void
     */
    protected function errorLogin(User $user)
    {
        if ($user) {
            $user->setFailureLoginCount($user->getFailureLoginCount() + 1);

            if ($user->getFailureLoginCount() >= 5) {
                $this->addFlash("danger", "Ce compte a été bloqué suite à de nombreux échecs de connexion. Veuillez-vous rapprocher d'un administrateur.");
            }
            $this->manager->flush();
        }
        return $this->addFlash("danger", "Identifiant ou mot de passe incorrect.");
    }

    /**
     * Création du mot de passe par l'utilisateur à sa première connexion
     * 
     * @Route("/login/after_login", name="security_after_login")
     * @return Response
     */
    public function afterLogin(): Response
    {
        $this->addFlash("success", "Bonjour " . $this->user->getFirstname() . " !");

        if ($this->user->getLoginCount() == 1) {
            return $this->redirectToRoute("security_init_password");
        }
        return $this->redirectToRoute("home");
    }

    /**
     * Création du mot de passe par l'utilisateur à sa première connexion
     * 
     * @Route("/login/init_password", name="security_init_password")
     * @param Request $request
     * @param UserResetPassword $user
     * @return Response
     */
    public function initPassword(Request $request, UserInitPassword $userInitPassword = null): Response
    {
        $userInitPassword = new UserInitPassword();

        $form = $this->createForm(InitPasswordType::class, $userInitPassword);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $hashPassword = $this->encoder->encodePassword($this->user, $userInitPassword->getPassword());
            $this->user->setPassword($hashPassword);

            $this->manager->flush();

            $this->addFlash("success", "Votre mot de passe a été modifié !");
            return $this->redirectToRoute("home");
        }

        return $this->render("security/initPassword.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Fiche de l'utilisateur connecté
     * 
     * @Route("/user", name="user_show", methods="GET|POST")
     * @param UserChangePassword $userChangePassword
     * @param Request $request
     * @return Response
     */
    public function showCurrentUser(UserChangeInfo $userChangeInfo = null, UserChangePassword $userChangePassword = null, Request $request): Response
    {
        $userChangeInfo->setEmail($this->user->getEmail())
            ->setPhone($this->user->getPhone())
            ->setPhone2($this->user->getPhone2());

        $form = $this->createForm(UserChangeInfoType::class, $userChangeInfo);
        $form->handleRequest($request);

        $userChangePassword = new UserChangePassword();

        $formPassword = $this->createForm(ChangePasswordType::class, $userChangePassword);
        $formPassword->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateInfoCurrentUser($userChangeInfo);
        }

        if ($formPassword->isSubmitted()) {
            $this->updatePasswordCurrentUser($formPassword, $userChangePassword);
        }

        return $this->render("app/user.html.twig", [
            "user" => $this->user,
            "form" => $form->createView(),
            "formPassword" => $formPassword->createView(),
        ]);
    }

    /**
     * Met à jour les coordonnées de l'utilisateur connecté
     */
    protected function updateInfoCurrentUser($userChangeInfo)
    {
        $this->user->setEmail($userChangeInfo->getEmail())
            ->setPhone($userChangeInfo->getPhone())
            ->setPhone2($userChangeInfo->getPhone2())
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->user);

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
    }

    /**
     * Met à jour le mot de passe de l'utilisateur connecté
     */
    protected function updatePasswordCurrentUser($formPassword, $userChangePassword)
    {
        if ($formPassword->isValid()) {

            $hashPassword = $this->encoder->encodePassword($this->user, $userChangePassword->getNewPassword());
            $this->user->setPassword($hashPassword);

            $this->manager->flush();

            return $this->addFlash("success", "Votre mot de passe a été mis à jour !");
        }
        return $this->addFlash("danger ", "Le mot de passe ou la confirmation est invalide.");
    }

    /**
     * @Route("/admin/user/{id}", name="security_user") 
     */
    public function editUser(User $user, Request $request)
    {
        $form = $this->createForm(SecurityUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->user);

            $this->manager->persist($user);
            $this->manager->flush();

            $this->addFlash("success", "Le compte utilisateur a été modifié.");

            return $this->redirectToRoute("security_user", [
                "id" => $user->getId(),
            ]);
        }

        return $this->render("security/securityUser.html.twig", [
            "form" => $form->createView(),
        ]);
    }

    /**
     * Page dans le cas d'un mot de passe oublié
     * 
     * @Route("/login/forgot_password", name="security_forgot_password")
     * @param Request $request
     * @param UserResetPassword $userResetPassword
     * @param MailNotification $notification
     * @return Response
     */
    public function forgotPassword(Request $request, UserResetPassword $userResetPassword = null, MailNotification $notification): Response
    {
        $userResetPassword = new UserResetPassword();

        $form = $this->createForm(ForgotPasswordType::class, $userResetPassword);

        $form->handleRequest($request);

        // Vérifie si l'utilisateur existe
        $user = $this->repo->findOneBy([
            "username" => $userResetPassword->getUsername(),
            "email" => $userResetPassword->getEmail(),
        ]);
        // Si le formulaire est soumis
        if ($form->isSubmitted()) {
            // Vérifie si l'utilisateur existe
            if ($user) {
                // Génère un token
                $token = bin2hex(random_bytes(32));
                // Enregistre le token dans la base
                $user->setToken($token)
                    ->setTokenCreatedAt(new \DateTime());

                $this->manager->flush();

                // Envoie l'email
                if (strchr($_SERVER["HTTP_HOST"], "127.0.0.1")) {
                    $notification->reinitPassword($user);
                } else {
                    $notification->reinitPassword2($user);
                }

                $this->addFlash("success",  "Un mail vous a été envoyé. Si vous n'avez rien reçu, merci de vérifier dans vos courriers indésirables.");
                return $this->redirectToRoute("security_login");
            } else {
                $this->addFlash("danger", "Le login ou l'adresse email sont incorrects.");
            }
        }
        return $this->render("security/forgotPassword.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Réinitialise le mot de passe de l'utilisateur
     * 
     * @Route("/login/reinit_password", name="security_reinit_password")
     * @param Request $request
     * @param UserResetPassword $user
     * @return Response
     */
    public function reinitPassword(Request $request, UserResetPassword $user = null): Response
    {
        $userResetPassword = new UserResetPassword();

        $form = $this->createForm(ReinitPasswordType::class, $userResetPassword);

        $form->handleRequest($request);

        // Si le formulaire est soumis
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si l'utilisateur existe avec le même token
            $user = $this->repo->findOneBy([
                "username" => $userResetPassword->getUsername(),
                "email" => $userResetPassword->getEmail(),
                "token" => $request->get("token"),
            ]);
            // Si l'utilisateur existe
            if ($user) {
                // Calcule l'intervalle entre le moment de demande de réinitialisation et maintenant
                $interval = date_timestamp_get(new \DateTime()) - date_timestamp_get($user->getTokenCreatedAt());
                $delay = 5 * 60; // 5 minutes x 60 secondes
                // Si le lien de réinitialisaiton est toujours valide
                if ($interval < $delay) {
                    $hashPassword = $this->encoder->encodePassword($user, $userResetPassword->getPassword());
                    // Met à jour le nouveau mot de passe
                    $user->setPassword($hashPassword)
                        ->setToken(null)
                        ->setTokenCreatedAt(null);

                    $this->manager->flush();

                    $this->addFlash("success", "Votre mot de passe a été réinitialisé !");
                    return $this->redirectToRoute("security_login");
                } else {
                    $this->addFlash("danger", "Le lien de réinitialisation est périmé.");
                }
            } else {
                $this->addFlash("danger", "Le login ou l'adresse email sont incorrects.");
            }
        }
        return $this->render("security/reinitPassword.html.twig", [
            "form" => $form->createView()
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
    public function logout()
    {
        $this->addFlash("notice", "Vous êtes déconnecté.");
    }
}
