<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder) {
        $user = new User();

        $formRegistration = $this->createForm(RegistrationType::Class, $user);

        $formRegistration->handleRequest($request);

        if ($formRegistration->isSubmitted() && $formRegistration->isValid()) {
            $hashPassword = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashPassword);

            $user->setCreationDate(new \DateTime());

            $manager->persist($user);
            $manager->flush();

            return $this->redictToRoute("security_login");
        }


        return $this->render("security/registration.html.twig", [
            "formRegistration" => $formRegistration->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login() {
        return $this->render("security/login.html.twig");
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() {}

}
