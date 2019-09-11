<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\PeopleGroup;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Repository\PersonRepository;
use App\Form\PersonType;
use App\Form\PeopleGroupType;


class ListPeopleController extends AbstractController
{
    /**
     * @Route("/list/people", name="app")
     */
    public function index(PersonRepository $repo) {
        // $repo = $this->getDoctrine()->getRepository(Person::class);
        $people = $repo->findAll();

        dump($people);

        return $this->render("app/index.html.twig", [
            "controller_name" => "ListPeopleController",
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
     * @Route("/list/group/new", name="create_people_group")
     * @Route("/list/group/{id}", name="peopleGroupCard")
     */
    public function formPeopleGroup(PeopleGroup $peopleGroup = NULL, Request $request, ObjectManager $manager) {
        
        if (!$peopleGroup) {
            $peopleGroup = new peopleGroup();
        }

        $form = $this->createForm(PeopleGroupType::class, $peopleGroup);

        $form->handleRequest($request);

        dump($peopleGroup);

        if($form->isSubmitted() && $form->isValid()) {
            if(!$peopleGroup->getId()) {
                $peopleGroup->setCreationDate(new \DateTime());
            }
            $peopleGroup->setUpdateDate(new \DateTime());
            $manager->persist($peopleGroup);
            $manager->flush();

            return $this->redirectToRoute("peopleGroupCard", ["id" => $peopleGroup->getId()]);
        }

        return $this->render("app/peopleGroupCard.html.twig", [
            "formPeopleGroup" => $form->createView(),
            "editMode" => $peopleGroup->getId() != NULL,
            "peopleGroup" => $peopleGroup,
        ]);
    }

    
    /**
     * @Route("/list/person/new", name="create_person")
     * @Route("/list/person/{id}", name="personCard")
     */
    public function formPerson(PersonRepository $repo, Person $person = NULL, Request $request, ObjectManager $manager) {
        
        if (!$person) {
            $person = new Person();
        }

        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        dump($person);

        if($form->isSubmitted() && $form->isValid()) {
            if(!$person->getId()) {
                $person->setCreationDate(new \DateTime());
                $person->addRolesPerson();
            } else {
                $person->removeRolesPerson();
            }
            $person->setUpdateDate(new \DateTime());
            $manager->persist($person);
            $manager->flush();

            return $this->redirectToRoute("personCard", ["id" => $person->getId()]);
        }

        // Donne l'ID groupe ménage de la personne
        // $peopleGroups = $person->getPeopleGroups();
        // foreach($peopleGroups as $peopleGroup) {
        //     $peopleGroupId = $peopleGroup->getid();
        // }
        // $repo = $this->getDoctrine()->getRepository(Person::class);

        // $people = $repo->findByPeopleGroup($person->getPeopleGroups());

        // dump($people);

        return $this->render("app/personCard.html.twig", [
            "formPerson" => $form->createView(),
            "editMode" => $person->getId() != NULL,
            "person" => $person,
            // "people" => $people
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