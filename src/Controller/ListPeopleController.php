<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;

use App\Form\GroupPeopleType;
use App\Form\PersonType;

use App\Repository\PersonRepository;
use App\Repository\GroupPeopleRepository;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ListPeopleController extends AbstractController
{
    private $manager;
    private $security;
    private $session;

    public function __construct(ObjectManager $manager, Security $security, SessionInterface $session)
    {
        $this->manager = $manager;
        $this->session = $session;
        $this->security = $security;
    }
    /**
     * @Route("/list/people", name="app")
     * @return Response
     */
    public function index(PersonRepository $repo): Response
    {
        // $repo = $this->getDoctrine()->getRepository(Person::class);
        $people = $repo->findAll();

        return $this->render("app/index.html.twig", [
            "controller_name" => "ListPeopleController",
            "people" => $people,
            "current_menu" => "list_people"
        ]);
    }

    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function home(): Response
    {
        return $this->render("app/home.html.twig", [
            "title" => "Bienvenue sur l'application de suivi social d'ESPERER 95",
            "current_menu" => "home"
        ]);
    }

    /**
     * @Route("/group/new", name="create_group_people")
     * @Route("/group/{id}", name="group_people_card")
     * @return Response
     */
    public function formGroupPeople(GroupPeople $groupPeople = NULL, Request $request, GroupPeopleRepository $repo): Response
    {
        if (!$groupPeople) {
            $groupPeople = new groupPeople();
        } else {
            $this->session->set("groupPeople", $groupPeople);
            // $this->session->set("groupPeople", [
            //     "id" => $groupPeople->getId(),
            //     "getFamilyTypologyType" => $groupPeople->listFamilyTypology(),
            //     "nbPeople" => $groupPeople->getNbPeople(),
            // ]);
        }

        dump($groupPeople);

        $formGroupPeople = $this->createForm(GroupPeopleType::class, $groupPeople);

        $formGroupPeople->handleRequest($request);

        if($formGroupPeople->isSubmitted() && $formGroupPeople->isValid()) {

            $user = $this->security->getUser();

            if(!$groupPeople->getId()) {
                $groupPeople->setCreatedAt(new \DateTime());
                $groupPeople->setCreatedBy($user->getid());
                $this->addFlash(
                    "success",
                    "Le ménage a été enregistré."
                );
            } else {
                $this->addFlash(
                    "success",
                    "Les modifications ont été enregistrées."
                );
            }
            $groupPeople->setUpdatedAt(new \DateTime());
            $groupPeople->setUpdatedBy($user->getid());
            $this->manager->persist($groupPeople);
            $this->manager->flush();

            return $this->redirectToRoute("group_people_card", ["id" => $groupPeople->getId()]);
        }

        return $this->render("app/groupPeopleCard.html.twig", [
            "form_group_people" => $formGroupPeople->createView(),
            "edit_mode" => $groupPeople->getId() != NULL,
            "group_people" => $groupPeople,
            "current_menu" => "new_group"
        ]);
    }

        /**
     * @Route("/group/remove_person/{id}", name="remove_person")
     * @return Response
     */
    public function removePerson(GroupPeople $groupPeople = NULL, Request $request, GroupPeopleRepository $repo): Response
    {
        $this->session->get("groupPeople")->removeRolePerson($rolePerson);

        return $this->redirectToRoute("groupPeopleCard", ["id" => $this->session->get("groupPeople")->getId()]);
    }

    /**
     * @Route("/person/new", name="create_person")
     * @Route("/group/person/{slug}-{id}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"})
     * @return Response
     */
    public function formPerson(GroupPeople $groupPeople = NULL, PersonRepository $repo, Person $person = NULL, RolePerson $rolePerson = NULL, Request $request) 
    {
        if (!$person) {
            $person = new Person();
        }

        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        $groupPeople = $this->session->get("groupPeople");


        if($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();

            if(!$person->getId()) {
                
                $person->setCreatedAt(new \DateTime())
                        ->setCreatedBy($user->getid());

                $rolePerson = new RolePerson();
                $rolePerson->setHead(FALSE)
                            ->setGroupPeople($groupPeople)
                            ->setRole(1);
                
                $this->manager->persist($rolePerson);

                $person->addRolesPerson($rolePerson);
                
                $this->addFlash(
                    "success",
                    "La personne est enregistrée."
                );
            } else {
                // $person->removeRolesPerson();
                $person->setUpdatedAt(new \DateTime());
                $person->setUpdatedBy($user->getid());
                $this->addFlash(
                    "success",
                    "Les modifications ont été enregistrées."
                );
            }

            $this->manager->persist($person);
            $this->manager->flush();

            return $this->redirectToRoute("person_show", ["id" => $person->getId()]);
        }

        return $this->render("app/personCard.html.twig", [
            "form_person" => $form->createView(),
            "edit_mode" => $person->getId() != NULL,
            "person" => $person,
        ]);
    }
}