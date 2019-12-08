<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(ObjectManager $manager, Security $security, SessionInterface $session)
    {
        $this->manager = $manager;
        $this->security = $security;
        $this->session = $session;
    }

    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function home(UserRepository $repo): Response
    {
        return $this->render("app/home.html.twig", [
            "current_menu" => "home"
        ]);
    }
}
