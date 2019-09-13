<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\Common\Persistence\ObjectManager;

use App\Entity\PeopleGroup;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Repository\PersonRepository;
use App\Repository\PeopleGroupRepository;
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
     * @Route("/list/group/{id}/person/{id}", name="peopleGroupCard")
     */
    public function editPersonFromGroup(PeopleGroup $peopleGroup = NULL, Request $request, ObjectManager $manager) {

    }


    /**
     * @Route("/list/group/new", name="create_people_group")
     * @Route("/list/group/{id}", name="peopleGroupCard")
     */
    public function formPeopleGroup(PeopleGroup $peopleGroup = NULL, Request $request, ObjectManager $manager, PeopleGroupRepository $repo) {
        
        if (!$peopleGroup) {
            $peopleGroup = new peopleGroup();
        }

        // $form = $this->createFormBuilder($peopleGroup->getRolePeople())
        // ->add("lastname", NULL, [
        //     "label" => "Nom"
        // ])
        // ->add("firstname", NULL, [
        //     "label" => "Prénom"
        // ])
        // ->add("birthdate", DateType::class, [
        //     "label" => "Date de naissance",
        //     "widget" => "single_text",
        //     "required" => false
            
        // ])
        // ->add("gender", ChoiceType::class, [
        //     "label" => "Sexe",
        //     "choices" => [
        //         "-- Sélectionner --" => NULL,
        //         "Femme" => 1,
        //         "Homme" => 2,
        //     ],
        // ])
        // ->add("rolesPerson", ChoiceType::class, [
        //     "label" => "Rôle",
        //     "attr" => [
        //         "class" => "col-md-6"
        //     ],
        //     "choices" => [
        //         "-- Sélectionner --" => NULL,
        //         "DP" => 1,
        //         "Conjoint(e)" => 2,
        //         "Enfant" => 3,
        //         "Autre" => 4
        //     ],
        // ])
        // ->getForm();

        $group = $repo->findPeopleFromGroup($peopleGroup);
        foreach($group as $rolePerson) {
            dump($rolePerson);
            foreach($rolePerson as $person) {
                dump($person);
            }
        }

        $formPeopleGroup = $this->createForm(PeopleGroupType::class, $peopleGroup);

        $formPeopleGroup->handleRequest($request);

        dump($peopleGroup);

        if($formPeopleGroup->isSubmitted() && $formPeopleGroup->isValid()) {
            if(!$peopleGroup->getId()) {
                $peopleGroup->setCreationDate(new \DateTime());
            }
            $peopleGroup->setUpdateDate(new \DateTime());
            $manager->persist($peopleGroup);
            $manager->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Les modifications ont bien été enregistrée.');

            return $this->redirectToRoute("peopleGroupCard", ["id" => $peopleGroup->getId()]);
        }

        return $this->render("app/peopleGroupCard.html.twig", [
            "formPeopleGroup" => $formPeopleGroup->createView(),
            "editMode" => $peopleGroup->getId() != NULL,
            "peopleGroup" => $peopleGroup,
            // "people" => $people
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
                // $person->addRolesPerson();
            } else {
                // $person->removeRolesPerson();
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