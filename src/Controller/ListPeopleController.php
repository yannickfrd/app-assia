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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @Route("/list/groupPeople", name="list_groups_people")
     * @return Response
     */
    public function listGroupsPeople(PersonRepository $repo): Response
    {
        // $repo = $this->getDoctrine()->getRepository(Person::class);
        $people = $repo->findAll();

        return $this->render("app/listGroupsPeople.html.twig", [
            "controller_name" => "ListPeopleController",
            "people" => $people,
            "current_menu" => "list_groups_people"
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
            // $this->session->set("groupPeople", $groupPeople);
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
            "group_people" => $groupPeople,
            "form" => $formGroupPeople->createView(),
            "edit_mode" => $groupPeople->getId() != NULL,
            "current_menu" => "new_group"
        ]);
    }

    /**
     * @Route("/group/{group_id}/person/remove-{person_id}_{role_person_id}_{_token}", name="remove_person", methods="GET")
     * @ParamConverter("groupPeople", options={"id" = "group_id"})
     * @ParamConverter("rolePerson", options={"id" = "role_person_id"})
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function removePerson(GroupPeople $groupPeople, RolePerson $rolePerson, Person $person, Request $request)
    {
        if ($this->isCsrfTokenValid("remove" . $rolePerson->getId(), $request->get("_token"))) {
            $groupPeople->removeRolePerson($rolePerson);
    
            $this->manager->flush();
    
            $this->addFlash(
                "warning",
                $person->getFirstname() . " a été retiré".  $this->gender($person->getGender()) . " du ménage."
            );
        } else {
            $this->addFlash(
                "danger",
                "Une erreur s'est produite."
            );
        }
        return $this->redirectToRoute("group_people_card", ["id" => $groupPeople->getId()]);
    }

    /**
     * @Route("/group/{group_id}/person/new", name="create_person", methods="GET|POST")
     * @Route("/group/{group_id}/person/{person_id}-{slug}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @ParamConverter("groupPeople", options={"id" = "group_id"})
     * @ParamConverter("person", options={"id" = "person_id"})
     * @return Response
     */
    public function formPerson(GroupPeople $groupPeople, PersonRepository $repo, Person $person = NULL, RolePerson $rolePerson = NULL, Request $request): Response
    {
        if (!$person) {
            $person = new Person();
        }

        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        // $groupPeople = $this->session->get("groupPeople");

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
                    $person->getFirstname() . " a été ajouté".  $this->gender($person->getGender()) . " au ménage."
                );

            } else {
                $this->addFlash(
                    "success",
                    "Les modifications ont été enregistrées."
                );
            }

            $person->setUpdatedAt(new \DateTime());
            $person->setUpdatedBy($user->getid());

            $this->manager->persist($person);
            $this->manager->flush();

            return $this->redirectToRoute("group_people_card", ["id" => $groupPeople->getId()]);   
            // return $this->redirectToRoute("person_show", [
            //     "group_id" => $groupPeople->getId(), 
            //     "person_id" => $person->getId(),
            //     "slug" => $person->getSlug()
            //     ]);
        }

        return $this->render("app/personCard.html.twig", [
            "group_people" =>$groupPeople,
            "person" => $person,
            "form" => $form->createView(),
            "edit_mode" => $person->getId() != NULL
        ]);
    }

    /**
     * Accorde en fonction du sexe de la personne (féminin, masculin)
     * @return String
     */
    private function gender($gender): String
    {
        if($gender == 1) {
            return "e";
        } else {
            return "";
        }
    }
}