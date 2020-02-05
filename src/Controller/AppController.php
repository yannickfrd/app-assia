<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    private $currentUser;
    private $repo;

    public function __construct(Security $security, UserRepository $repo)
    {
        $this->currentUser = $security->getUser();
        $this->repo = $repo;
    }

    /**
     * @Route("/home", name="home")
     * @Route("/")
     * @return Response
     */
    public function home(): Response
    {
        $user = $this->repo->findUserById($this->currentUser);

        return $this->render("app/home.html.twig", [
            "user" => $user,
            "current_menu" => "home"
        ]);
    }
}
