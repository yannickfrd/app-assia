<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\Person;
use App\Repository\PersonRepository;
use App\Form\PersonType;

class ListPeopleController extends AbstractController
{
    /**
     * @Route("/list/people", name="app")
     */
    public function index(PersonRepository $repo) {
        // $repo = $this->getDoctrine()->getRepository(Person::class);
        $people = $repo->findAll();

        return $this->render('app/index.html.twig', [
            'controller_name' => 'ListPeopleController',
            "people" => $people
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home() {
        return $this->render("app/home.html.twig", [
            "title" => "Bienvenue sur l'application de suivi social d'ESPERER 95",
        ]);
    }

    /**
     * @Route("/list/person/new", name="create_person")
     * @Route("/list/person/{id}", name="personCard")
     */
    public function formPerson(Person $person = NULL, Request $request, ObjectManager $manager) {
        
        if (!$person) {
            $person = new Person();
        }

        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        dump($person);

        if($form->isSubmitted() && $form->isValid()) {
            date_default_timezone_set("Europe/Paris");
            if(!$person->getId()) {
                $person->setCreationDate(new \DateTime());
            }
            $person->setUpdateDate(new \DateTime());
            $manager->persist($person);
            $manager->flush();

            return $this->redirectToRoute("personCard", ["id" => $person->getId()]);
        }

        return $this->render("app/personCard.html.twig", [
            "formPerson" => $form->createView(),
            "editMode" => $person->getId() != NULL
        ]);
    }

    /**
     * @Route("/list/person/{id}", name="personCard")
     */
    // public function personShow(Person $person) {
    //     $repo = $this->getDoctrine()->getRepository(Person::class);
    //     $person = $repo->find($id);
    //     return $this->render("app/personCard.html.twig", [
    //         "person" => $person
    //     ]);

    //     $form = $this->formPerson($person, "update");

    //     return $this->render("app/personCard.html.twig", [
    //         "formPerson" => $form->createView()
    //     ]);
    // }

}