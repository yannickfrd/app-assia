<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Model\UserChangeInfo;
use App\Form\User\UserType;
use App\Repository\UserRepository;
use App\Form\Model\UserResetPassword;

use App\Form\User\UserChangeInfoType;

use App\Form\Model\UserChangePassword;

use App\Notification\MailNotification;
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
    private $security;
    private $repo;

    public function __construct(EntityManagerInterface $manager, Security $security, UserPasswordEncoderInterface $encoder, UserRepository $repo)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
        $this->security = $security;
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

        // Vérifie et compte les erreurs de validation
        // $errors = $validator->validate($user);
        // $nbErrors = count($errors);
        // if ($nbErrors > 0) {
        //     $errorsString = (string) $errors;
        // }

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setLoginCount(0);

            $hashPassword = $this->encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashPassword)
                ->setCreatedAt(new \DateTime())
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

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
     * @Route("/admin/user/{id}", name="security_user") 
     */
    public function editUser(User $user, Request $request)
    {
        $form = $this->createForm(SecurityUserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

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
     * @Route("/login", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $user = $this->repo->findOneBy(["username" => $lastUsername]);

        // if ($user && $user->getFailureLoginCount() >= 5) {
        //     $this->addFlash("danger", "Ce compte utilisateur a été bloqué suite à de nombreux échecs de connexion. Veuillez-vous rapprocher d'un administrateur.");
        // }

        if ($error) {
            $this->addFlash("danger", "Identifiant ou mot de passe incorrect.");

            // if ($user) {
            //     $failureLoginCount = $user->getFailureLoginCount() + 1;
            //     $user->setFailureLoginCount($failureLoginCount);
            //     $this->manager->flush();
            // } else {
            //     $this->addFlash("danger", "Identifiant ou mot de passe incorrect.");
            // }
        }

        return $this->render("security/login.html.twig", [
            "last_username" => $lastUsername,
            "error" => $error
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
        $user = $this->repo->findUserById($this->security->getUser());

        $userChangeInfo->setEmail($user->getEmail())
            ->setPhone($user->getPhone())
            ->setPhone2($user->getPhone2());

        $form = $this->createForm(UserChangeInfoType::class, $userChangeInfo);
        $form->handleRequest($request);

        $userChangePassword = new UserChangePassword();

        $formPassword = $this->createForm(ChangePasswordType::class, $userChangePassword);
        $formPassword->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateInfoCurrentUser($user, $userChangeInfo);
        }

        if ($formPassword->isSubmitted()) {
            $this->updatePasswordCurrentUser($formPassword, $user, $userChangePassword);
        }

        return $this->render("app/user.html.twig", [
            "user" => $user,
            "form" => $form->createView(),
            "formPassword" => $formPassword->createView(),
        ]);
    }

    /**
     * Met à jour les coordonnées de l'utilisateur connecté
     */
    protected function updateInfoCurrentUser($user, $userChangeInfo)
    {
        $user->setEmail($userChangeInfo->getEmail())
            ->setPhone($userChangeInfo->getPhone())
            ->setPhone2($userChangeInfo->getPhone2())
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->security->getUser());

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
    }

    /**
     * Met à jour le mot de passe de l'utilisateur connecté
     */
    protected function updatePasswordCurrentUser($formPassword, $user, $userChangePassword)
    {
        if ($formPassword->isValid()) {

            $hashPassword = $this->encoder->encodePassword($user, $userChangePassword->getNewPassword());
            $user->setPassword($hashPassword);

            $this->manager->flush();

            return $this->addFlash("success", "Votre mot de passe a été mis à jour !");
        }
        return $this->addFlash("danger ", "Le mot de passe ou la confirmation est invalide.");
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
                if ($_SERVER["HTTP_HOST"] == "127.0.0.1:8001") {
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
