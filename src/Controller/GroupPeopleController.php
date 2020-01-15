<?php

namespace App\Controller;

use App\Service\Agree;

use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Form\Utils\Choices;

use App\Entity\GroupPeopleSearch;
use App\Form\Group\GroupPeopleType;

use App\Repository\RolePersonRepository;

use Doctrine\ORM\EntityManagerInterface;

use App\Form\Group\GroupPeopleSearchType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class GroupPeopleController extends AbstractController
{
    private $manager;
    private $security;

    public function __construct(EntityManagerInterface $manager, Security $security)
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
        // $rolePeople->setPageRange(5);
        $rolePeople->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/listGroupsPeople.html.twig", [
            "role_people" => $rolePeople,
            "form" => $form->createView()
        ]);
    }

    /**
     * Voir la fiche d'un groupe
     * 
     * @Route("/group/{id}", name="group_people_show")
     * @param GroupPeople $groupPeople
     * @param Request $request
     * @return Response
     */
    public function editGroupPeople(GroupPeople $groupPeople, Request $request): Response
    {
        $formGroupPeople = $this->createForm(GroupPeopleType::class, $groupPeople);
        $formGroupPeople->handleRequest($request);

        if ($formGroupPeople->isSubmitted() && $formGroupPeople->isValid()) {
            $groupPeople->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());
            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        }

        return $this->render("app/groupPeople.html.twig", [
            "form" => $formGroupPeople->createView(),
        ]);
    }

    /**
     * Ajoute une personne dans une groupe
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
                "choices" => Choices::getChoices(RolePerson::ROLE),
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

                // Compte le nombre de personnes dans le groupe
                $nbPeople = $groupPeople->getRolePerson()->count();
                $groupPeople->setNbPeople($nbPeople + 1);

                $this->manager->flush();

                $this->addFlash(
                    "success",
                    $person->getFirstname() . " a été ajouté" . Agree::gender($person->getGender()) . " au groupe."
                );
            } else {
                $this->addFlash(
                    "warning",
                    $person->getFirstname() . " est déjà associé" . Agree::gender($person->getGender()) . " au groupe."
                );
            }
        } else {
            $this->addFlash(
                "danger",
                "Une erreur s'est produite."
            );
        }
        return $this->redirectToRoute("group_people_show", ["id" => $groupPeople->getId()]);
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
        if (!$this->isGranted("ROLE_ADMIN")) {
            return $this->json([
                "code" => 403,
                "msg" => "Vous n'avez pas les droits faire cette action. Demandez à un administrateur de votre service.",
                "data" => null
            ], 200);
        }

        // Vérifie si le token est valide avant de retirer la personne du groupe
        if ($this->isCsrfTokenValid("remove" . $rolePerson->getId(), $request->get("_token"))) {
            // Compte le nombre de personnes dans le groupe
            $nbPeople = $groupPeople->getRolePerson()->count();
            // Vérifie que le groupe est composé de plus d'1 personne

            if ($rolePerson->getHead()) {
                return $this->json([
                    "code" => null,
                    "msg" => "Le/la demandeur/euse principal·e ne peut pas être retiré du groupe.",
                    "data" => null
                ], 200);
            }
            $groupPeople->removeRolePerson($rolePerson);
            $groupPeople->setNbPeople($nbPeople - 1);
            $this->manager->flush();

            return $this->json([
                "code" => 200,
                "msg" => $person->getFirstname() . " a été retiré" .  Agree::gender($person->getGender()) . " du groupe.",
                "data" => $nbPeople - 1
            ], 200);
        }

        return $this->json([
            "code" => null,
            "msg" => "Une erreur s'est produite.",
            "data" => $nbPeople - 1
        ], 200);
    }
}
