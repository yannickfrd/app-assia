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

use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Repository\PersonRepository;
use App\Repository\GroupPeopleRepository;
use App\Form\PersonType;
use App\Form\GroupPeopleType;

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
     * @Route("/list/group/{id}/person/{id}", name="groupPeopleCard")
     */
    public function editPersonFromGroup(GroupPeople $groupPeople = NULL, Request $request, ObjectManager $manager) {

    }


    /**
     * @Route("/list/group/new", name="create_group_people")
     * @Route("/list/group/{id}", name="groupPeopleCard")
     */
    public function formGroupPeople(GroupPeople $groupPeople = NULL, Request $request, ObjectManager $manager, GroupPeopleRepository $repo) {
        
        if (!$groupPeople) {
            $groupPeople = new groupPeople();
        }

        $formGroupPeople = $this->createForm(GroupPeopleType::class, $groupPeople);

        $formGroupPeople->handleRequest($request);

        dump($groupPeople);

        if($formGroupPeople->isSubmitted() && $formGroupPeople->isValid()) {
            if(!$groupPeople->getId()) {
                $groupPeople->setCreationDate(new \DateTime());
            }
            $groupPeople->setUpdateDate(new \DateTime());
            $manager->persist($groupPeople);
            $manager->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Les modifications ont bien été enregistrée.');

            return $this->redirectToRoute("groupPeopleCard", ["id" => $groupPeople->getId()]);
        }

        return $this->render("app/groupPeopleCard.html.twig", [
            "formGroupPeople" => $formGroupPeople->createView(),
            "editMode" => $groupPeople->getId() != NULL,
            "groupPeople" => $groupPeople,
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
        // $groupPeoples = $person->getGroupPeoples();
        // foreach($groupPeoples as $groupPeople) {
        //     $groupPeopleId = $groupPeople->getid();
        // }
        // $repo = $this->getDoctrine()->getRepository(Person::class);

        // $people = $repo->findByGroupPeople($person->getGroupPeoples());

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