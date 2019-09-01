<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class ListPeopleController extends AbstractController
{
    /**
     * @Route("/list/people", name="list_people")
     */
    public function index() {
        return $this->render('list_people/index.html.twig', [
            'controller_name' => 'ListPeopleController',
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home() {
        return $this->render("list_people/home.html.twig", [
            "title" => "Bienvenue sur l'application de suivi social d'ESPERER 95",
        ]);
    }

    /**
     * @Route("/list/person/1", name="person_show")
     */
    public function person() {
        return $this->render("list_people/person_show.html.twig");
    }
}
