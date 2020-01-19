<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

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
}
