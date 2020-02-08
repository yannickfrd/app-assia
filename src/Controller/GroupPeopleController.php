<?php

namespace App\Controller;

use App\Entity\Person;
use App\Service\Grammar;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Form\Model\GroupPeopleSearch;
use App\Form\RolePerson\RolePersonType;
use App\Repository\RolePersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\GroupPeople\GroupPeopleType;
use App\Repository\GroupPeopleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Form\GroupPeople\GroupPeopleSearchType;
use App\Service\Pagination;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class GroupPeopleController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, GroupPeopleRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des groupes de personnes
     * 
     * @Route("/groups_people", name="groups_people")
     * @param GroupPeopleSearch $groupPeopleSearch
     * @param Request $request
     * @param Pagination $pagination
     * @return Response
     */
    public function listGroupsPeople(GroupPeopleSearch $groupPeopleSearch = null, Request $request, Pagination $pagination): Response
    {
        $groupPeopleSearch = new GroupPeopleSearch();

        $form = $this->createForm(GroupPeopleSearchType::class, $groupPeopleSearch);
        $form->handleRequest($request);

        $groupsPeople = $pagination->paginate($this->repo->findAllGroupPeopleQuery($groupPeopleSearch), $request);

        return $this->render("app/listGroupsPeople.html.twig", [
            "groupsPeople" => $groupsPeople,
            "form" => $form->createView()
        ]);
    }

    /**
     * Modification d'un groupe
     * 
     * @Route("/group/{id}", name="group_people_show")
     * @param Request $request
     * @return Response
     */
    public function editGroupPeople($id, Request $request): Response
    {
        $groupPeople = $this->repo->findGroupPeopleById($id);

        $formGroupPeople = $this->createForm(GroupPeopleType::class, $groupPeople);
        $formGroupPeople->handleRequest($request);

        if ($formGroupPeople->isSubmitted() && $formGroupPeople->isValid()) {
            $this->updateGroupPeople($groupPeople);
        }

        return $this->render("app/groupPeople.html.twig", [
            "form" => $formGroupPeople->createView(),
        ]);
    }

    /**
     * Ajout d'une personne dans une groupe
     * 
     * @Route("/group/{id}/add/person/{person_id}", name="group_add_person")
     * @ParamConverter("person", options={"id" = "person_id"})
     * @param Person $person
     * @param RolePersonRepository $repo
     * @return Response
     */
    public function tryAddPersonInGroup($id, Person $person, RolePerson $rolePerson = null, RolePersonRepository $repoRolePerson, Request $request): Response
    {
        $groupPeople = $this->repo->findGroupPeopleById($id);

        $rolePerson = new RolePerson;

        $form = $this->createForm(RolePersonType::class, $rolePerson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addPersonInGroup($groupPeople, $rolePerson, $person, $repoRolePerson);
        } else {
            $this->addFlash("danger", "Une erreur s'est produite.");
        }

        return $this->redirectToRoute("group_people_show", ["id" => $groupPeople->getId()]);
    }

    /**
     * Retire la personne du groupe
     * 
     * @Route("/role_person/{id}/remove/{_token}", name="role_person_remove", methods="GET")
     * @param RolePerson $rolePerson
     * @param Request $request
     * @return Response
     */
    public function tryRemovePersonInGroup(RolePerson $rolePerson, Request $request): Response
    {
        if (!$this->isGranted("ROLE_ADMIN")) {
            return $this->accessDenied();
        }
        // Vérifie si le token est valide avant de retirer la personne du groupe
        if ($this->isCsrfTokenValid("remove" . $rolePerson->getId(), $request->get("_token"))) {
            return $this->removePersonInGroup($rolePerson);
        }
        return $this->errorMessage();
    }

    /**
     * Met à jour un groupe de personnes
     * 
     * @param GroupPeople $groupPeople
     */
    protected function updateGroupPeople(GroupPeople $groupPeople)
    {
        $groupPeople->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
    }

    /**
     * Ajoute une personne dans le groupe
     * 
     * @param GroupPeople $groupPeople
     * @param RolePerson $rolePerson
     * @param person $person
     * @param RolePersonRepository $repoRolePerson
     */
    protected function addPersonInGroup(GroupPeople $groupPeople, RolePerson $rolePerson, person $person, RolePersonRepository $repoRolePerson)
    {
        // Si la personne est asssociée, ne fait rien, créé la liaison
        if ($this->personExists($groupPeople, $person, $repoRolePerson)) {
            return $this->addFlash("warning", $person->getFullname() . " est déjà associé" . Grammar::gender($person->getGender()) . " au groupe.");
        }

        $rolePerson
            ->setHead(false)
            ->setGroupPeople($groupPeople)
            ->setCreatedAt(new \DateTime());

        $person->addRolesPerson($rolePerson);

        $this->manager->persist($rolePerson);

        $nbPeople = $groupPeople->getRolePerson()->count(); // Compte le nombre de personnes dans le groupe
        $groupPeople->setNbPeople($nbPeople + 1);

        $this->manager->flush();

        return $this->addFlash("success", $person->getFullname() . " a été ajouté" . Grammar::gender($person->getGender()) . " au groupe.");
    }

    /**
     *  Vérifie si la personne est déjà rattachée à ce groupe
     * 
     * @param GroupPeople $groupPeople
     * @param Person $person
     * @param RolePersonRepository $repoRolePerson
     * @return RolePerson|null
     */
    protected function personExists(GroupPeople $groupPeople, Person $person, RolePersonRepository $repoRolePerson)
    {
        return $repoRolePerson->findOneBy([
            "person" => $person->getId(),
            "groupPeople" => $groupPeople->getId()
        ]);
    }

    /**
     * Retire une personne d'un groupe
     * 
     * @param RolePerson $rolePerson
     * @return Response
     */
    protected function removePersonInGroup(RolePerson $rolePerson): Response
    {
        $person = $rolePerson->getPerson();
        $groupPeople = $rolePerson->getGroupPeople();

        $nbPeople = $groupPeople->getRolePerson()->count(); // // Compte le nombre de personnes dans le groupe
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
            "msg" => $person->getFullname() . " a été retiré" .  Grammar::gender($person->getGender()) . " du groupe.",
            "data" => $nbPeople - 1
        ], 200);
    }

    /**
     * Retourne un message d'accès refusé
     * 
     * @return Response
     */
    protected function accessDenied(): Response
    {
        return $this->json([
            "code" => 403,
            "alert" => "danger",
            "msg" => "Vous n'avez pas les droits pour cette action. Demandez à un administrateur de votre service.",
        ], 200);
    }

    /**
     * Retourne un message d'erreur au format JSON
     * 
     * @return Response
     */
    protected function errorMessage(): Response
    {
        return $this->json([
            "code" => 403,
            "alert" => "danger",
            "msg" => "Une erreur s'est produite.",
        ], 200);
    }
}
