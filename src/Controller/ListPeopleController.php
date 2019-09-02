<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\Person;
use App\Repository\PersonRepository;

class ListPeopleController extends AbstractController
{
    /**
     * @Route("/list/people", name="list_people")
     */
    public function index(PersonRepository $repo) {
        // $repo = $this->getDoctrine()->getRepository(Person::class);
        $people = $repo->findAll();

        return $this->render('list_people/index.html.twig', [
            'controller_name' => 'ListPeopleController',
            "people" => $people
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
     * @Route("/list/person/new", name="create_person")
     */
    public function createPerson(Request $request, ObjectManager $manager) {
        $person = new Person();

        $form = $this->createFormBuilder($person)
                     ->add("firstname", TextType::class, [
                         "attr" => [
                             "class" => "form-control mb-4",
                             "placeholder" => "Prénom"
                         ]
                     ])
                     ->add("lastname", TextType::class, [
                        "attr" => [
                            "class" => "form-control mb-4",
                            "placeholder" => "Nom"
                        ]
                    ])
                    ->add("birthdate")
                     ->add("sex")
                     ->add("nationality")
                     ->add("comment")
                     ->getForm();

        return $this->render("list_people/person_show.html.twig", [
            "formPerson" => $form->createView()
        ]);
    }

    /**
     * @Route("/list/person/{id}", name="person_show")
     */
    public function personShow(Person $person) {
        // $repo = $this->getDoctrine()->getRepository(Person::class);
        // $person = $repo->find($id);
        return $this->render("list_people/person_show.html.twig", [
            "person" => $person
        ]);
    }
}
