<?php

namespace App\Controller;

use App\Utils\Agree;
use App\Entity\Person;
use App\Form\PersonType;

use App\Entity\RolePerson;
use App\Entity\GroupPeople;

use App\Form\GroupPeopleType;
use App\Repository\PersonRepository;

use App\Repository\GroupPeopleRepository;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ListPeopleController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(ObjectManager $manager, Security $security)
    {
        $this->manager = $manager;
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
        $people = $repo->findAll();

        return $this->render("app/listGroupsPeople.html.twig", [
            "controller_name" => "ListPeopleController",
            "people" => $people,
            "current_menu" => "list_groups_people"
        ]);
    }

    /**
     * @Route("/group/new", name="create_group_people")
     * @Route("/group/{id}", name="group_people")
     * @return Response
     */
    public function formGroupPeople(GroupPeople $groupPeople = NULL, Request $request, GroupPeopleRepository $repo): Response
    {
        if (!$groupPeople) {
            $groupPeople = new GroupPeople();
        } else {
            // $this->session->set("groupPeople", $groupPeople);
        }

        $formGroupPeople = $this->createForm(GroupPeopleType::class, $groupPeople);

        dump($groupPeople);

        $formGroupPeople->handleRequest($request);

        if($formGroupPeople->isSubmitted() && $formGroupPeople->isValid()) {

            $user = $this->security->getUser();

            if(!$groupPeople->getId()) {
                $groupPeople
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($user->getid());

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
            $groupPeople
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user->getid());

            $this->updateNbPeople($groupPeople);
            $this->manager->persist($groupPeople);
            $this->manager->flush();

            return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);
        }

        return $this->render("app/groupPeople.html.twig", [
            "group_people" => $groupPeople,
            "form" => $formGroupPeople->createView(),
            "edit_mode" => $groupPeople->getId() != NULL,
            "current_menu" => "new_group"
        ]);
    }

    /**
     * @Route("/group/{id}/person/remove-{person_id}_{role_person_id}_{_token}", name="remove_person", methods="GET")
     * @ParamConverter("rolePerson", options={"id" = "role_person_id"})
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function removePerson(GroupPeople $groupPeople, RolePerson $rolePerson, Person $person, Request $request)
    {
        if ($this->isCsrfTokenValid("remove" . $rolePerson->getId(), $request->get("_token"))) {
            $groupPeople->removeRolePerson($rolePerson);

            $this->updateNbPeople($groupPeople);
            $this->manager->persist($groupPeople);
            $this->manager->flush();
    
            $this->addFlash(
                "warning",
                $person->getFirstname() . " a été retiré".  Agree::gender($person->getGender()) . " du ménage."
            );
        } else {
            $this->addFlash(
                "danger",
                "Une erreur s'est produite."
            );
        }
        return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);
    }

    // Met à jour le le nombre de personnes indiqué dans le ménage
    public function updateNbPeople(GroupPeople $groupPeople) {
        $nbPerson = count($groupPeople->getRolePerson());
        $groupPeople->setNbPeople($nbPerson);
    }
}