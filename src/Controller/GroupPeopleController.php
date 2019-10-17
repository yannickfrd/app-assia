<?php

namespace App\Controller;

use App\Utils\Agree;

use App\Entity\Person;

use App\Entity\RolePerson;
use App\Entity\GroupPeople;

use App\Form\GroupPeopleType;

use App\Entity\GroupPeopleSearch;
use App\Form\GroupPeopleSearchType;

use App\Repository\RolePersonRepository;
use App\Repository\GroupPeopleRepository;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class GroupPeopleController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(ObjectManager $manager, Security $security)
    {
        $this->manager = $manager;
        $this->security = $security;
    }

    /**
     * @Route("/list/group_people", name="list_groups_people")
     * @return Response
     */
    public function listGroupsPeople(RolePersonRepository $repo, GroupPeopleSearch $groupPeopleSearch = null, Request $request, PaginatorInterface $paginator): Response
    {
        // $rolePeople = $repo->findAll();
        $groupPeopleSearch = new GroupPeopleSearch();

        $form = $this->createForm(GroupPeopleSearchType::class, $groupPeopleSearch);
        $form->handleRequest($request);

        $rolePeople =  $paginator->paginate(
            $repo->findAllRolePeopleQuery($groupPeopleSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $rolePeople->setPageRange(5);
        $rolePeople->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/listGroupsPeople.html.twig", [
            "controller_name" => "ListPeopleController",
            "role_people" => $rolePeople,
            "form" => $form->createView(),
            "current_menu" => "list_groups_people"
        ]);
    }

    /**
     * @Route("/group/new", name="create_group_people")
     * @Route("/group/{id}", name="group_people")
     * @return Response
     */
    public function formGroupPeople(GroupPeople $groupPeople = null, Request $request, GroupPeopleRepository $repo): Response
    {
        if (!$groupPeople) {
            $groupPeople = new GroupPeople();
        }

        $formGroupPeople = $this->createForm(GroupPeopleType::class, $groupPeople);
        $formGroupPeople->handleRequest($request);

        if ($formGroupPeople->isSubmitted() && $formGroupPeople->isValid()) {
            if (!$groupPeople->getId()) {
                $groupPeople->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->security->getUser());
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
            $groupPeople->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());
            $this->manager->persist($groupPeople);
            $this->manager->flush();

            return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);
        }

        return $this->render("app/groupPeople.html.twig", [
            "group_people" => $groupPeople,
            "form" => $formGroupPeople->createView(),
            "edit_mode" => $groupPeople->getId() != null,
            "current_menu" => "new_group"
        ]);
    }

    /**
     * Ajoute une personne dans une groupe ménage
     * 
     * @Route("/group/{id}/add/person/{person_id}", name="group_add_person")
     * @ParamConverter("person", options={"id" = "person_id"})
     * @param GroupPeople $groupPeople
     * @param Person $person
     * @param RolePerson $rolePerson
     * @param RolePersonRepository $repo
     * @return Response
     */
    public function addPersonInGroup(GroupPeople $groupPeople, Person $person, RolePerson $rolePerson = null, RolePersonRepository $repo, Request $request): Response
    {
        // Vérifie si la personne est déjà associée à ce groupe
        $personExist = $repo->findOneBy([
            "person" => $person->getId(),
            "groupPeople" => $groupPeople->getId()
        ]);

        $rolePerson = new RolePerson;

        $formRolePerson = $this->createFormBuilder($rolePerson)
            ->add("role", ChoiceType::class, [
                "choices" => $this->getChoices(RolePerson::ROLE),
            ])
            ->getForm();

        $formRolePerson->handleRequest($request);

        if ($formRolePerson->isSubmitted() && $formRolePerson->isValid()) {
            // Si la personne n'est pas associée, ajout de la liaison, sinon ne fait rien
            if (!$personExist) {
                $rolePerson
                    ->setHead(false)
                    ->setGroupPeople($groupPeople)
                    ->setCreatedAt(new \DateTime());

                $person->addRolesPerson($rolePerson);

                $this->manager->persist($rolePerson);
                $this->manager->flush();

                $this->addFlash(
                    "success",
                    $person->getFirstname() . " a été ajouté" . Agree::gender($person->getGender()) . " au ménage."
                );
            } else {
                $this->addFlash(
                    "warning",
                    $person->getFirstname() . " est déjà associé" . Agree::gender($person->getGender()) . " au ménage."
                );
            }
        } else {
            $this->addFlash(
                "danger",
                "Une erreur s'est produite."
            );
        }
        return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);
    }


    public function getchoices($const)
    {
        foreach ($const as $key => $value) {
            $output[$value] = $key;
        }
        return $output;
    }


    /**
     * Retire la personne du groupe
     * 
     * @Route("/group/{id}/person/remove-{person_id}_{role_person_id}_{_token}", name="remove_person", methods="GET")
     * @ParamConverter("rolePerson", options={"id" = "role_person_id"})
     * @ParamConverter("person", options={"id" = "person_id"})
     * @param GroupPeople $groupPeople
     * @param RolePerson $rolePerson
     * @param Request $request
     * @return Response
     */
    public function removePerson(GroupPeople $groupPeople, RolePerson $rolePerson, Person $person, Request $request): Response
    {
        // Vérifie si le token est valide avant de retirer la personne du groupe
        if ($this->isCsrfTokenValid("remove" . $rolePerson->getId(), $request->get("_token"))) {
            // Compte le nombre de personnes dans le ménage
            $nbPeople = $groupPeople->getRolePerson()->count();
            // Vérifie que le ménage est composé de plus d'1 personne
            if ($nbPeople == 1) {
                return $this->msgFlash(null, "Un ménage doit être composé d'au moins une personne.", null,  200);
            } else {
                $groupPeople->removeRolePerson($rolePerson);
                $groupPeople->setNbPeople($nbPeople - 1);
                $this->manager->flush();

                return $this->msgFlash(200, $person->getFirstname() . " a été retiré" .  Agree::gender($person->getGender()) . " du ménage.", $nbPeople - 1, 200);
            }
        } else {
            return $this->msgFlash(null, "Une erreur s'est produite.", null,  200);
        }
    }

    public function msgFlash($code, $msg, $data, $status): Response
    {
        return $this->json([
            "code" => $code,
            "msg" => $msg,
            "data" => $data
        ], $status);
    }
}
