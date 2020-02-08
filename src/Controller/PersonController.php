<?php

namespace App\Controller;

use App\Entity\Person;
use App\Service\Grammar;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Export\PersonExport;
use App\Form\Person\PersonType;
use App\Form\Model\PersonSearch;
use App\Repository\PersonRepository;
use App\Form\Person\PersonSearchType;
use App\Form\Person\PersonNewGroupType;
use App\Form\RolePerson\RolePersonType;
use App\Form\Person\RolePersonGroupType;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Person\PersonRolePersonType;
use App\Service\Pagination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PersonController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, PersonRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des personnes
     * 
     * @Route("/people", name="people")
     * @Route("/new_support/search/person", name="new_support_search_person")
     * @param Request $request
     * @param PersonSearch $personSearch
     * @param Pagination $pagination
     * @return Response
     */
    public function listPeople(Request $request, PersonSearch $personSearch = null, Pagination $pagination): Response
    {
        $personSearch = new PersonSearch();

        $form = $this->createForm(PersonSearchType::class, $personSearch);
        $form->handleRequest($request);

        if ($personSearch->getExport()) {
            return $this->exportData($personSearch);
        }

        $people = $pagination->paginate($this->repo->findAllPeopleQuery($personSearch, $request->query->get("search-person")), $request);

        return $this->render("app/listPeople.html.twig", [
            "personSearch" => $personSearch,
            "form" => $form->createView(),
            "people" => $people ?? null
        ]);
    }

    /**
     * Rechercher une personne pour l'ajouter dans un groupe
     * 
     * @Route("/group/{id}/search_person", name="group_search_person")
     * @param GroupPeople $groupPeople
     * @param PersonSearch $personSearch
     * @param Request $request
     * @param Pagination $pagination
     * @return Response
     */
    public function searchPersonToAdd(GroupPeople $groupPeople, PersonSearch $personSearch = null, Request $request, Pagination $pagination): Response
    {
        $personSearch = new PersonSearch();
        $rolePerson = new RolePerson;

        $form = $this->createForm(PersonSearchType::class, $personSearch);
        $form->handleRequest($request);

        $formRolePerson = $this->createForm(RolePersonType::class, $rolePerson);
        $formRolePerson->handleRequest($request);

        if ($request->query->all()) {
            $people = $pagination->paginate($this->repo->findAllPeopleQuery($personSearch), $request);
        }

        return $this->render("app/listPeople.html.twig", [
            "form" => $form->createView(),
            "form_role_person" => $formRolePerson->createView() ?? null,
            "group_people" => $groupPeople,
            "personSearch" => $personSearch,
            "people" => $people ?? null
        ]);
    }

    /**
     * Nouvelle personne
     * 
     * @Route("/person/new", name="person_new", methods="GET|POST")
     * @param RolePerson $rolePersonequest
     * @return Response
     */
    public function newPerson(RolePerson $rolePerson = null, Request $request): Response
    {
        $rolePerson = new RolePerson();

        $form = $this->createForm(RolePersonGroupType::class, $rolePerson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createPerson($rolePerson);
        }
        return $this->render("app/person.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Crée une nouvelle personne dans un group existant
     * 
     * @Route("/group/{id}/person/new", name="group_create_person", methods="GET|POST")
     * @param GroupPeople $groupPeople
     * @param RolePerson $rolePerson
     * @param Request $request
     * @return Response
     */
    public function newPersonInGroup(GroupPeople $groupPeople, RolePerson $rolePerson = null, Request $request): Response
    {
        $rolePerson = new RolePerson();

        $form = $this->createForm(PersonRolePersonType::class, $rolePerson);
        $form->handleRequest($request);

        $person = $rolePerson->getPerson();

        if ($form->isSubmitted() && $form->isValid()) {
            $personExists = $this->personExists($person);
            // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
            if ($personExists) {
                $this->addFlash("warning", "Attention : " . $person->getFullname() . " existe déjà !");
                return $this->redirectToRoute("person_show", ["id" => $personExists->getId()]);
            }
            $this->createPersonInGroup($person, $rolePerson, $groupPeople);
            return $this->redirectToRoute("group_people_show", ["id" => $groupPeople->getId()]);
        }
        return $this->render("app/person.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'une personne
     * 
     * @Route("/group/{id}/person/{person_id}-{slug}", name="group_person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     * @param GroupPeople $groupPeople
     * @param Person $person
     * @param Request $request
     * @return Response
     */
    public function editPersonInGroup(GroupPeople $groupPeople, $person_id, Request $request): Response
    {
        $person = $this->repo->findPersonById($person_id);

        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        $rolePerson = new RolePerson();
        $formNewGroup = $this->createForm(PersonNewGroupType::class, $rolePerson, [
            "action" => $this->generateUrl("person_new_group", ["id" => $person->getId()]),
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $person->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());
            $this->manager->flush();
        }

        return $this->render("app/person.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "form_new_group" => $formNewGroup->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Met à jour les informations d'une personne via Ajax
     * 
     * @Route("/person/{id}/edit", name="person_edit_ajax", methods="GET|POST")
     * @param Person $person
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function editPerson(Person $person, Request $request, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->updatePerson($person);
        }
        return $this->errorMessage($validator, $form);
    }

    /**
     * Voir la fiche individuelle
     * 
     * @Route("/person/{id}-{slug}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @Route("/person/{id}", name="person_show", methods="GET|POST")
     * @param Person $person
     * @param RolePerson $rolePerson
     * @param Request $request
     * @return Response
     */
    public function personShow(Person $person, RolePerson $rolePerson = null, Request $request): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        // Formulaire pour ajouter un nouveau groupe à la personne
        $rolePerson = new RolePerson();
        $formNewGroup = $this->createForm(PersonNewGroupType::class, $rolePerson, [
            "action" => $this->generateUrl("person_new_group", ["id" => $person->getId()]),
        ]);

        return $this->render("app/person.html.twig", [
            "form" => $form->createView(),
            "form_new_group" => $formNewGroup->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Ajoute un nouveau groupe à la personne
     * 
     * @Route("/person/{id}/new_group", name="person_new_group", methods="GET|POST")
     * @param Person $person
     * @param RolePerson $rolePerson
     * @param Request $request
     */
    public function newGroupToPerson(Person $person, RolePerson $rolePerson = null, Request $request)
    {
        $rolePerson = new RolePerson;

        $form = $this->createForm(PersonNewGroupType::class, $rolePerson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createNewGroupToPerson($person, $rolePerson);
        }
        $this->addFlash("danger", "Une erreur s'est produite");
        return $this->redirectToRoute("person_show", ["id" => $person->getId()]);
    }

    /**
     * Permet de trouver les personnes par le mode de recherche instannée AJAX
     *
     * @Route("/search/person", name="search_person", methods="GET")
     * @param Person $person
     * @param Request $request
     * @return Response
     */
    public function searchPerson(Request $request): Response
    {
        if ($request->query->get("search")) {
            $search = $request->query->get("search");
        } else {
            $search = null;
        }

        $people = $this->repo->findPeopleByResearch($search);
        $nbResults = count($people);

        if ($nbResults) {
            foreach ($people as $person) {
                $results[] = [
                    "id" => $person->getId(),
                    "fullname" => $person->getFullname(),
                ];
            }
            return $this->json([
                "nb_results" => $nbResults,
                "results" => $results
            ], 200);
        }
        return $this->json([
            "nb_results" => $nbResults,
            "results" => "Aucun résultat."
        ], 200);
    }

    /**
     * Export des données
     * 
     * @param PersonSearch $personSearch
     */
    protected function exportData(PersonSearch $personSearch)
    {
        $people = $this->repo->findPeopleToExport($personSearch);
        $export = new PersonExport();
        return $export->exportData($people);
    }

    /**
     * Crée une nouvelle personne
     * 
     * @param RolePerson $rolePerson
     */
    protected function createPerson(RolePerson $rolePerson)
    {
        $person = $rolePerson->getPerson();
        $groupPeople = $rolePerson->getGroupPeople();

        // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
        if ($this->personExists($person)) {
            return $this->addFlash("warning", "Attention : " . $person->getFullname() . " existe déjà !");
        }

        $now = new \DateTime();

        $groupPeople->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());
        $this->manager->persist($groupPeople);

        $rolePerson->setHead(true)
            ->setCreatedAt($now)
            ->setGroupPeople($groupPeople);
        $this->manager->persist($rolePerson);

        $person->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser())
            ->addRolesPerson($rolePerson);
        $this->manager->persist($person);

        $this->manager->flush();

        $this->addFlash("success", $person->getFullname() . " a été créé" .  Grammar::gender($person->getGender()) . ", ainsi que son groupe.");
        return $this->redirectToRoute("group_people_show", ["id" => $groupPeople->getId()]);
    }

    /**
     * Vérifie si la personne existe déjà
     * 
     * @param Person $person
     */
    protected function personExists(Person $person)
    {
        return $this->repo->findOneBy([
            "lastname" => $person->getLastname(),
            "firstname" => $person->getFirstname(),
            "birthdate" => $person->getBirthdate()
        ]);
    }

    /**
     * Crée une personne avec son rôle
     * 
     * @param Person $person
     * @param GroupPeople $groupPeople
     * @param RolePerson $rolePerson
     */
    protected function createPersonInGroup(Person $person, RolePerson $rolePerson = null, GroupPeople $groupPeople)
    {
        $now = new \DateTime();

        $rolePerson->setHead(false)
            ->setCreatedAt($now)
            ->setGroupPeople($groupPeople);
        $this->manager->persist($rolePerson);

        $person->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser())
            ->addRolesPerson($rolePerson);
        $this->manager->persist($person);

        $nbPeople = $groupPeople->getRolePerson()->count();
        $groupPeople->setNbPeople($nbPeople + 1);

        $this->manager->flush();

        $this->addFlash("success", $person->getFullname() . " a été ajouté" .  Grammar::gender($person->getGender()) . " au groupe.");
    }

    /** 
     * Met à jour la personne
     * 
     * @param Person $person
     * @return Response
     */
    protected function updatePerson(Person $person): Response
    {
        $now = new \DateTime();

        $person->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "alert" => "success",
            "msg" =>  "Les modifications ont été enregistrées.",
            "user" => $this->getUser()->getFullname(),
            "date" => date_format($now, "d/m/Y à H:i")
        ], 200);
    }

    /**
     * Crée un nouveau groupe à la personne
     * 
     * @param Person $person
     * @param RolePerson $rolePerson
     */
    protected function createNewGroupToPerson(Person $person, RolePerson $rolePerson)
    {
        $now = new \DateTime();

        $groupPeople = $rolePerson->getGroupPeople();

        $groupPeople->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());
        $this->manager->persist($groupPeople);

        $rolePerson->setHead(true)
            ->setCreatedAt($now)
            ->setGroupPeople($groupPeople);
        $this->manager->persist($rolePerson);

        $person->addRolesPerson($rolePerson)
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());
        $this->manager->persist($person);

        $this->manager->flush();

        $this->addFlash("success", "Le nouveau groupe a été créé.");

        return $this->redirectToRoute("group_people_show", ["id" => $groupPeople->getId()]);
    }

    /**
     * Retourne un message d'erreur au format JSON
     * 
     * @param ValidatorInterface $validator
     * @return Response
     */
    protected function errorMessage(ValidatorInterface $validator = null, $form): Response
    {
        $errors = $validator->validate($form);
        foreach ($errors as $error) {
            $msg[] = $error->getMessage();
        }

        return $this->json([
            "code" => 403,
            "alert" => "danger",
            "msg" => "Une erreur s'est produite : " . join($msg, " ")
        ], 200);
    }
}
