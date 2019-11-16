<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\RoleUser;
use App\Entity\UserResetPass;
use App\Form\RegistrationType;
use App\Form\ForgotPasswordType;
use App\Form\ReinitPasswordType;
use App\Form\RegistrationUserType;
use App\Repository\UserRepository;
use App\Notification\MailNotification;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    private $manager;


    public function __construct(ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
    }

    /**
     * @Route("/inscription", name="security_registration") 
     */
    public function registration(Request $request, ValidatorInterface $validator)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::Class, $user);

        $form->handleRequest($request);

        dump($form);


        // Vérifie et compte les erreurs de validation
        $errors = $validator->validate($user);
        $nbErrors = count($errors);

        // if ($nbErrors > 0) {
        //     $errorsString = (string) $errors;
        //     $message = $nbErrors . " Erreur(s).";
        // }

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setLoginCount(0);
            $this->addFlash(
                "success",
                "Votre compte a été créé !"
            );

            $hashPassword = $this->encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashPassword);

            $user->setCreatedAt(new \DateTime());

            $this->manager->persist($user);
            $this->manager->flush();

            return $this->redirectToRoute("security_login");
        }

        return $this->render("security/registration.html.twig", [
            "form" => $form->createView(),
            // "message" => $message,
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            dump($error);
            $this->addFlash(
                "danger",
                "Identifiant ou mot de passe incorrect."
            );
        }

        return $this->render("security/login.html.twig", [
            "last_username" => $lastUsername,
            "error" => $error
        ]);
    }

    /**
     * @Route("/forgot_password", name="security_forgot_password")
     */
    public function forgotPassword(Request $request, UserResetPass $user = null, UserRepository $repo, MailNotification $notification): Response
    {
        $user = new UserResetPass();

        $form = $this->createForm(ForgotPasswordType::class, $user);

        $form->handleRequest($request);

        // Vérifie si l'utilisateur existe
        $userExists = $repo->findOneBy([
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
        ]);
        // Si le formulaire est soumis
        if ($form->isSubmitted()) {
            // Vérifie si l'utilisateur existe
            if ($userExists) {
                $user = $userExists;
                // Génère un token
                $token = bin2hex(random_bytes(32));
                // Enregistre le token dans la base
                $user->setToken($token)
                    ->setTokenCreatedAt(new \DateTime());

                $this->manager->flush();

                // Envoie l'email
                $notification->reinitPassword($user);

                $this->addFlash(
                    "success",
                    "Un mail vous a été envoyé."
                );
                return $this->redirectToRoute("security_forgot_password");
            } else {
                $this->addFlash(
                    "danger",
                    "Le login ou l'adresse email sont incorrects."
                );
            }
        }
        return $this->render("security/forgotPassword.html.twig", [
            "form" => $form->createView()
        ]);
    }

    /**
     * Réinitialise le mot de passe de l'utilisateur
     * 
     * @Route("/reinit_password", name="security_reinit_password")
     * @param Request $request
     * @param UserResetPass $user
     * @param UserRepository $repo
     * @return Response
     */
    public function reinitPassword(Request $request, UserResetPass $user = null, UserRepository $repo): Response
    {
        $user = new UserResetPass();

        $form = $this->createForm(ReinitPasswordType::class, $user);

        $form->handleRequest($request);

        // Si le formulaire est soumis
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si l'utilisateur existe avec le même token
            $userExists = $repo->findOneBy([
                "username" => $user->getUsername(),
                "email" => $user->getEmail(),
                "token" => $request->get("token"),
            ]);
            // Si l'utilisateur existe
            if ($userExists) {
                // Calcule l'intervalle entre le moment de demande de réinitialisation et maintenant
                $interval = date_timestamp_get(new \DateTime()) - date_timestamp_get($userExists->getTokenCreatedAt());
                $delay = 5 * 60; // 5 minutes x 60 secondes

                // Si le lien de réinitialisaiton est toujours valide
                if ($interval < $delay) {
                    $newPassword = $user->getPassword();
                    $user = $userExists;
                    $hashPassword = $this->encoder->encodePassword($user, $newPassword);
                    // Met à jout le nouveau mot de passe
                    $user->setPassword($hashPassword)
                        ->setToken(null)
                        ->setTokenCreatedAt(null);

                    $this->manager->flush();

                    $this->addFlash(
                        "success",
                        "Votre mot de passe a été réinitialisé !"
                    );
                    return $this->redirectToRoute("security_login");
                } else {
                    $this->addFlash(
                        "danger",
                        "Le lien de réinitialisation est périmé."
                    );
                }
            } else {
                $this->addFlash(
                    "danger",
                    "Le login ou l'adresse email sont incorrects."
                );
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
    { }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        $this->addFlash(
            "notice",
            "Vous êtes déconnecté."
        );
    }
}
