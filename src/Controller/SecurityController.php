<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;

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
    /**
     * @Route("/inscription", name="security_registration") 
     */
    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::Class, $user);

        $form->handleRequest($request);

        // Vérifie et compte les erreurs de validation
        $errors = $validator->validate($user);
        $nbErrors = count($errors);

        if ($nbErrors > 0) {
            $errorsString = (string) $errors;
            $message = $nbErrors . " Erreur(s).";
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setLoginCount(0);
            $this->addFlash(
                "success",
                "Votre compte a été créé !"
            );

            $hashPassword = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashPassword);

            $user->setCreatedAt(new \DateTime());

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute("security_login");
        }

        return $this->render("security/registration.html.twig", [
            "form" => $form->createView(),
            "message" => $message,
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
