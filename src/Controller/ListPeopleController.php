<?php

namespace App\Controller;

use App\Entity\Person;

use App\Form\PersonType;
use App\Entity\RolePerson;

use App\Entity\GroupPeople;
use App\Form\GroupPeopleType;
use App\Repository\PersonRepository;
use App\Repository\GroupPeopleRepository;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ListPeopleController extends AbstractController
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }
    /**
     * @Route("/list/people", name="app")
     */
    public function index(PersonRepository $repo) {
        // $repo = $this->getDoctrine()->getRepository(Person::class);
        $people = $repo->findAll();

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
     * @Route("/group/new", name="create_group_people")
     * @Route("/group/{id}", name="groupPeopleCard")
     */
    public function formGroupPeople(GroupPeople $groupPeople = NULL, Request $request, ObjectManager $manager, GroupPeopleRepository $repo) {
        
        if (!$groupPeople) {
            $groupPeople = new groupPeople();
        } else {
            $this->session->set("groupPeople", [
                "id" => $groupPeople->getId(),
                "listFamilyTypology" => $groupPeople->listFamilyTypology(),
                "nbPeople" => $groupPeople->getNbPeople(),
            ]);
        }

        $formGroupPeople = $this->createForm(GroupPeopleType::class, $groupPeople);

        $formGroupPeople->handleRequest($request);

        if($formGroupPeople->isSubmitted() && $formGroupPeople->isValid()) {
            if(!$groupPeople->getId()) {
                $groupPeople->setCreationDate(new \DateTime());
            }
            $groupPeople->setUpdateDate(new \DateTime());
            $manager->persist($groupPeople);
            $manager->flush();

            $this->addFlash(
                "success",
                "Les modifications ont bien été enregistrées."
            );

            return $this->redirectToRoute("groupPeopleCard", ["id" => $groupPeople->getId()]);
        }

        return $this->render("app/groupPeopleCard.html.twig", [
            "formGroupPeople" => $formGroupPeople->createView(),
            "editMode" => $groupPeople->getId() != NULL,
            "groupPeople" => $groupPeople,
        ]);
    }

    /**
     * @Route("/person/new", name="create_person")
     * @Route("/group/person/{id}", name="personCard")
     */
    public function formPerson(PersonRepository $repo, Person $person = NULL, Request $request, ObjectManager $manager) {
        
        if (!$person) {
            $person = new Person();
        }

        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        // dump($person);

        if($form->isSubmitted() && $form->isValid()) {
            if(!$person->getId()) {
                $person->setCreationDate(new \DateTime());
                // $person->addRolesPerson();
                $this->addFlash(
                    "success",
                    "La personne est enregistrée."
                );
            } else {
                // $person->removeRolesPerson();
                $this->addFlash(
                    "success",
                    "Les modifications ont été enregistrées."
                );
            }



            $person->setUpdateDate(new \DateTime());
            $manager->persist($person);
            $manager->flush();

            return $this->redirectToRoute("personCard", ["id" => $person->getId()]);
        }

        return $this->render("app/personCard.html.twig", [
            "formPerson" => $form->createView(),
            "editMode" => $person->getId() != NULL,
            "person" => $person,
        ]);
    }
}