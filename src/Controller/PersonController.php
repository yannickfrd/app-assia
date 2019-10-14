<?php

namespace App\Controller;

use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\GroupPeople;
use App\Entity\PersonSearch;

use App\Form\PersonSearchMinType;
use App\Form\PersonType;

use App\Utils\Agree;

use App\Repository\PersonRepository;
use App\Repository\RolePersonRepository;
use Knp\Component\Pager\PaginatorInterface;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PersonController extends AbstractController
{
    private $manager;
    private $repo;
    private $request;
    private $security;

    public function __construct(ObjectManager $manager, PersonRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Permet de rechercher une personne
     * 
     * @Route("/list/people", name="list_people")
     * @Route("/group/{id}/search/person", name="group_search_person")
     * @Route("/new_support/search/person", name="new_support_search_person")
     * @return Response
     */
    public function listPeople(PaginatorInterface $paginator, Request $request, PersonSearch $personSearch = NULL, GroupPeople $groupPeople = NULL): Response
    {
        $personSearch = new PersonSearch();

        $form = $this->createForm(PersonSearchMinType::class, $personSearch);
        $form->handleRequest($request);

        return $this->pagination($personSearch, $request, $groupPeople, $form, $paginator);
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
    public function addPersonInGroup(GroupPeople $groupPeople, Person $person, RolePerson $rolePerson = NULL, RolePersonRepository $repo): Response
    {
        // Vérifie si la personne est déjà associée à ce groupe
        $personExist = $repo->findOneBy([
            "person" => $person->getId(),
            "groupPeople" => $groupPeople->getId()
        ]);

        // Si elle n'est pas associée, ajout de la liaison, sinon ne fait rien
        if (!$personExist) {
            $rolePerson = new RolePerson;
            $rolePerson
                ->setHead(FALSE)
                ->setCreatedAt(new \DateTime())
                ->setGroupPeople($groupPeople)
                ->setRole(5);

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
        return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);
    }

    /**
     * Crée une nouvelle personne
     * 
     * @Route("/group/{id}/person/new", name="create_person", methods="GET|POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     * @return Response
     */
    public function newPerson(Person $person = NULL, GroupPeople $groupPeople = NULL, PersonRepository $repo, Request $request): Response
    {
        $person = new Person();

        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifie si la personne existe déjà dans la base de données
            $personExist = $repo->findOneBy([
                "lastname" => $person->getLastname(),
                "firstname" => $person->getFirstname(),
                "birthdate" => $person->getBirthdate()
            ]);
            // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
            if ($personExist) {
                $this->addFlash(
                    "warning",
                    "Attention : " . $person->getFirstname() . " " . $person->getLastname() . " existe déjà !"
                );
                return $this->redirectToRoute("person_show", ["id" => $personExist->getId()]);
            } else {
                $this->createPerson($person, $groupPeople);
            }
        }
        return $this->render("app/person.html.twig", [
            "group_people" => $groupPeople,
            "person" => $person,
            "form" => $form->createView(),
            "edit_mode" => $person->getId() != NULL
        ]);
    }

    /**
     * Crée une personne avec son rôle²
     *
     * @param Person $person
     * @param GroupPeople $groupPeople
     * @param RolePerson $rolePerson
     * @return Response
     */
    protected function createPerson(Person $person, GroupPeople $groupPeople, RolePerson $rolePerson = NULL): Response
    {
        $user = $this->security->getUser();

        $rolePerson = new RolePerson();
        $rolePerson->setHead(FALSE)
            ->setCreatedAt(new \DateTime())
            ->setGroupPeople($groupPeople)
            ->setRole(4);
        $this->manager->persist($rolePerson);

        $person->setCreatedAt(new \DateTime())
            ->setCreatedBy($user)
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($user)
            ->addRolesPerson($rolePerson);
        $this->manager->persist($person);

        $this->manager->flush();

        $this->addFlash(
            "success",
            $person->getFirstname() . " a été ajouté" .  Agree::gender($person->getGender()) . " au ménage."
        );

        return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);
        // return $this->redirectToRoute("person_show", [
        //     "id" => $groupPeople->getId(), 
        //     "person_id" => $person->getId(),
        //     "slug" => $person->getSlug()
        //     ]);
    }

    /**
     * Modifie une personne
     * 
     * @Route("/group/{id}/person/{person_id}-{slug}", name="group_person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     * @return Response
     */
    public function editPerson(GroupPeople $groupPeople = NULL, Person $person, RolePerson $rolePerson = NULL, Request $request): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();

            // $rolePerson ->setHead(FALSE)
            //             ->setRole();
            // $this->manager->persist($rolePerson);

            $person->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user);
            $this->manager->persist($person);

            $this->manager->flush();

            $this->addFlash(
                "success",
                "Les modifications ont été enregistrées."
            );

            return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);
        }

        return $this->render("app/person.html.twig", [
            "group_people" => $groupPeople,
            "person" => $person,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Voir la fiche de la personne
     * 
     * @Route("/person/{id}-{slug}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @Route("/person/{id}", name="person_show", methods="GET|POST")
     *  @return Response
     */
    public function personShow(Person $person, RolePerson $rolePerson = NULL, Request $request): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();

            $person->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user);
            $this->manager->persist($person);

            $this->manager->flush();

            $this->addFlash(
                "success",
                "Les modifications ont été enregistrées."
            );
        }

        return $this->render("app/person.html.twig", [
            "person" => $person,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }


    // Met en place la pagination du tableau et affiche le rendu
    protected function pagination($personSearch, $request, $groupPeople, $form, $paginator)
    {
        if ($request->query->get("search")) {
            $search = $request->query->get("search");
        } else {
            $search = NULL;
        }

        if ($request->query->all()) {
            $people =  $paginator->paginate(
                $this->repo->findAllPeopleQuery($personSearch, $search),
                $request->query->getInt("page", 1), // page number
                20 // limit per page
            );
            $people->setPageRange(3);
            $people->setCustomParameters([
                "align" => "right", // alignement de la pagination
            ]);
        } else {
            $people = NULL;
        }

        return $this->render("app/listPeople.html.twig", [
            "controller_name" => "PersonController",
            "group_people" => $groupPeople,
            "people" => $people,
            "personSearch" => $personSearch,
            "form" => $form->createView(),
            "current_menu" => "list_people"
        ]);
    }

    /**
     * Permet de trouver les personnes par le mode de recherche instannée
     *
     * @Route("/search/person", name="search_person")
     * 
     * @param Person $person
     * @param Request $request
     * @param PersonRepository $repo
     * @return Response
     */
    public function searchPerson(Request $request): Response
    {
        if ($request->query->get("search")) {
            $search = $request->query->get("search");
        } else {
            $search = NULL;
        }

        $people = $this->repo->findPeopleByResearch($search);
        $nbResults = count($people);

        if ($nbResults) {
            foreach ($people as $person) {
                $results[] = [
                    "id" => $person->getId(),
                    "lastname" => $person->getLastname(),
                    "firstname" => $person->getFirstname()
                ];
            }
            return $this->json([
                "nb_results" => $nbResults,
                "results" => $results
            ], 200);
        } else {
            return $this->json([
                "nb_results" => $nbResults,
                "results" => "Aucun résultat."
            ], 200);
        }
    }


    // /**
    //  * @Route("/search/person", name="person_search")
    //  */
    // public function personSearch(PersonSearch $personSearch = NULL, Request $request) 
    // {
    //     $personSearch = new PersonSearch();

    //     $form = $this->createForm(PersonSearchType::class, $personSearch);

    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->IsValid()) {
    //         return $this->redirectToRoute("list_people", [
    //         "personSearch" => $personSearch,
    //         ]);   
    //     }

    //     return $this->render("app/personSearch.html.twig", [
    //     "personSearch" =>$personSearch,
    //     "form" => $form->createView(),
    //     "current_menu" => "person_search"
    //     ]);
    // }
}
