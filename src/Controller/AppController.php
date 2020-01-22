<?php

namespace App\Controller;

use Twig\Environment;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;
use App\Notification\MailNotificationTest;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(EntityManagerInterface $manager, Security $security, SessionInterface $session)
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->session = $session;
    }

    /**
     * @Route("/home", name="home")
     * @Route("/")
     * @return Response
     */
    public function home(UserRepository $repo): Response
    {
        $user = $repo->findUserById($this->security->getUser());

        return $this->render("app/home.html.twig", [
            "user" => $user,
            "current_menu" => "home"
        ]);
    }

    /**
     * @Route("/email", name="email")
     * @Route("/")
     * @return Response
     */
    public function email(MailNotificationTest $mailNotificationTest, Environment $renderer): Response
    {
        $user = $this->security->getUser();

        $to = [
            "email" => "romain.madelaine@esperer-95.org",
            "name" => "Romain Madelaine"
        ];

        $htmlBody = $renderer->render(
            "emails/reinitPassword.html.twig",
            ["user" => $user]
        );
        $txtBody = $renderer->render(
            "emails/reinitPassword.txt.twig",
            ["user" => $user]
        );

        $message = $mailNotificationTest->send($to, "Esperer95-app : Réinitialisation du mot de passe", $htmlBody, null);

        $this->addFlash($message["type"], $message["message"]);

        return $this->redirectToRoute("home");
    }
}
