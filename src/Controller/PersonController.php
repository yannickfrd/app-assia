<?php

namespace App\Controller;

use App\Utils\Agree;
use App\Entity\Person;
use App\Form\PersonType;

use App\Entity\RolePerson;
use App\Entity\GroupPeople;

use App\Entity\PersonSearch;
use App\Form\RolePersonType;
use App\Form\PersonSearchType;
use App\Form\RolePersonGroupType;
use App\Repository\PersonRepository;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
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
     * @Route("/new_support/search/person", name="new_support_search_person")
     * @return Response
     */
    public function listPeople(Request $request, PersonSearch $personSearch = null, PaginatorInterface $paginator): Response
    {
        $personSearch = new PersonSearch();

        $form = $this->createForm(PersonSearchType::class, $personSearch);
        $form->handleRequest($request);

        $search = $request->query->get("search");


        if ($request->query->all()) {
            $people =  $paginator->paginate(
                $this->repo->findAllPeopleQuery($personSearch, $search),
                $request->query->getInt("page", 1), // page number
                20 // limit per page
            );
            $people->setPageRange(5);
            $people->setCustomParameters([
                "align" => "right", // alignement de la pagination
            ]);
        }

        return $this->render("app/listPeople.html.twig", [
            // "controller_name" => "PersonController",
            "people" => $people ?? null,
            "personSearch" => $personSearch,
            "form" => $form->createView(),
            "current_menu" => "list_people"
        ]);
        // return $this->pagination($personSearch, $request, $form, $paginator);
    }

    /**
     * Permet de rechercher une personne pour l'ajouter dans un group groupe
     * 
     * @Route("/group/{id}/search/person", name="group_search_person")
     * @return Response
     */
    public function groupSearchPerson(Request $request, PersonSearch $personSearch = null, GroupPeople $groupPeople = null, RolePerson $rolePerson = null, PaginatorInterface $paginator): Response
    {
        $personSearch = new PersonSearch();

        $formRolePerson = null;

        if ($groupPeople) {
            $formRolePerson = $this->createFormBuilder($rolePerson)
                ->add("role", ChoiceType::class, [
                    "choices" => $this->getChoices(RolePerson::ROLE),
                ])
                ->getForm();
        }

        $form = $this->createForm(PersonSearchType::class, $personSearch);
        $form->handleRequest($request);

        if ($request->query->all()) {
            $people =  $paginator->paginate(
                $this->repo->findAllPeopleQuery($personSearch, $search = null),
                $request->query->getInt("page", 1), // page number
                20 // limit per page
            );
            $people->setPageRange(5);
            $people->setCustomParameters([
                "align" => "right", // alignement de la pagination
            ]);
        }

        return $this->render("app/listPeople.html.twig", [
            // "controller_name" => "PersonController",
            "group_people" => $groupPeople,
            "people" => $people ?? null,
            "personSearch" => $personSearch,
            "form" => $form->createView(),
            "form_role_person" => $formRolePerson->createView(),
            "current_menu" => "list_people"
        ]);
        // return $this->pagination($personSearch, $request, $groupPeople, $form,  $formRolePerson, $paginator);
    }

    public function getchoices($const)
    {
        foreach ($const as $key => $value) {
            $output[$value] = $key;
        }
        return $output;
    }

    /**
     * Crée une nouvelle personne
     * 
     * @Route("/person/new", name="person_new", methods="GET|POST")
     * @param Person $person
     * @param RolePerson $rolePerson
     * @param GroupPeople $groupPeople
     * @param PersonRepository $repo
     * @param Request $request
     * @return Response
     */
    public function newPerson(Person $person = null, RolePerson $rolePerson = null, GroupPeople $groupPeople = null, PersonRepository $repo, Request $request): Response
    {
        $person = new Person();
        $rolePerson = new RolePerson();
        $groupPeople = new GroupPeople();

        $form = $this->createForm(RolePersonGroupType::class, $rolePerson);
        $form->handleRequest($request);

        $person = $rolePerson->getPerson();
        $groupPeople = $rolePerson->getGroupPeople();

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
            } else {
                $groupPeople->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->security->getUser())
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($this->security->getUser());
                $this->manager->persist($groupPeople);

                $rolePerson->setHead(true)
                    ->setCreatedAt(new \DateTime())
                    ->setGroupPeople($groupPeople);
                $this->manager->persist($rolePerson);

                $person->setCreatedAt(new \DateTime())
                    ->setCreatedBy($this->security->getUser())
                    ->setUpdatedAt(new \DateTime())
                    ->setUpdatedBy($this->security->getUser())
                    ->addRolesPerson($rolePerson);
                $this->manager->persist($person);

                $this->manager->flush();

                $this->addFlash(
                    "success",
                    $person->getFirstname() . " a été créé" .  Agree::gender($person->getGender()) . ", ainsi que son groupe groupe."
                );
                return $this->redirectToRoute("group_people_show", ["id" => $groupPeople->getId()]);
            }
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
     * @param Person $person
     * @param RolePerson $rolePerson
     * @param GroupPeople $groupPeople
     * @param PersonRepository $repo
     * @param Request $request
     * @return Response
     */
    public function newPersonInGroup(Person $person = null, RolePerson $rolePerson = null, GroupPeople $groupPeople, PersonRepository $repo, Request $request): Response
    {
        $person = new Person();
        $rolePerson = new RolePerson();

        $form = $this->createForm(RolePersonType::class, $rolePerson);
        $form->handleRequest($request);

        $person = $rolePerson->getPerson();

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
                $this->createPerson($person, $groupPeople, $rolePerson);
                return $this->redirectToRoute("group_people_show", ["id" => $groupPeople->getId()]);
            }
        } else {
            return $this->render("app/person.html.twig", [
                "group_people" => $groupPeople,
                "form" => $form->createView(),
                "edit_mode" => false
            ]);
        }
    }

    /**
     * Crée une personne avec son rôle²
     *
     * @param Person $person
     * @param GroupPeople $groupPeople
     * @param RolePerson $rolePerson
     */
    protected function createPerson(Person $person, GroupPeople $groupPeople, RolePerson $rolePerson = null)
    {
        $rolePerson->setHead(false)
            ->setCreatedAt(new \DateTime())
            ->setGroupPeople($groupPeople);
        $this->manager->persist($rolePerson);

        $person->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->security->getUser())
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->security->getUser())
            ->addRolesPerson($rolePerson);
        $this->manager->persist($person);

        $nbPeople = $groupPeople->getRolePerson()->count();
        $groupPeople->setNbPeople($nbPeople + 1);

        $this->manager->flush();

        $this->addFlash(
            "success",
            $person->getFirstname() . " a été ajouté" .  Agree::gender($person->getGender()) . " au groupe."
        );
    }

    /**
     * Modifie une personne
     * 
     * @Route("/group/{id}/person/{person_id}-{slug}", name="group_person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     * @param GroupPeople $groupPeople
     * @param Person $person
     * @param Request $request
     * @return Response
     */
    public function editPerson(GroupPeople $groupPeople, Person $person, Request $request, ValidatorInterface $validator): Response
    {
        $socialSupports = $person->getSocialSupports();

        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        // $nbErrors = count($validator->validate($form));

        if ($form->isSubmitted() && $form->isValid()) {

            $person->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        } elseif ($form->isSubmitted() && !$form->isValid()) {

            $this->addFlash("danger", "Les informations saisies sont invalides.");
        }

        return $this->render("app/person.html.twig", [
            "group_people" => $groupPeople,
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Met à jour les informations d'une personne via Ajax
     * 
     * @Route("/person/update-{id}", name="update_person", methods="GET|POST")
     * @param Person $person
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     */
    public function updatePerson(Person $person, Request $request, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        $now = new \DateTime();

        if ($form->isSubmitted() && $form->isValid()) {
            $person->setUpdatedAt($now)
                ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $alert = "success";
            $msg[] = "Les modifications ont été enregistrées.";
        } else {
            $alert = "danger";
            $errors = $validator->validate($form);
            foreach ($errors as $error) {
                $msg[] = $error->getMessage();
            }
        }
        return $this->json([
            "code" => 200,
            "alert" => $alert,
            "msg" => $msg,
            "user" => $this->getUser()->getUsername(),
            "date" => date_format($now, "d/m/Y à H:i")
        ], 200);
    }

    /**
     * Voir la fiche individuelle
     * 
     * @Route("/person/{id}-{slug}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @Route("/person/{id}", name="person_show", methods="GET|POST")
     *  @return Response
     */
    public function personShow(Person $person, RolePerson $rolePerson = null, Request $request): Response
    {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $person->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
        }

        return $this->render("app/person.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }


    // Met en place la pagination du tableau et affiche le rendu
    protected function pagination($personSearch, $request, $groupPeople, $form, $formRolePerson = null, $paginator)
    { }

    /**
     * Permet de trouver les personnes par le mode de recherche instannée
     *
     * @Route("/search/person", name="search_person")
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
            $search = null;
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
    // public function personSearch(PersonSearch $personSearch = null, Request $request) 
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
