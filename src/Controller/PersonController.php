<?php

namespace App\Controller;

use App\Utils\Agree;
use App\Entity\Person;
use App\Form\PersonType;
use App\Entity\RolePerson;

use App\Entity\GroupPeople;
use App\Entity\PersonSearch;
use App\Form\PersonSearchType;
use App\Form\PersonSearchMinType;
use App\Repository\PersonRepository;
use Knp\Component\Pager\PaginatorInterface;

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

class PersonController extends AbstractController
{
    private $manager;
    private $repo;
    private $security;

    public function __construct(ObjectManager $manager, PersonRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * @Route("/list/people", name="list_people")
     * @return Response
     */
    public function listPeople(PaginatorInterface $paginator, Request $request, PersonSearch $personSearch = NULL): Response
    {
        $personSearch = new PersonSearch();
               
        $form = $this->createForm(PersonSearchMinType::class, $personSearch);

        $form->handleRequest($request);

        $people =  $paginator->paginate(
            $this->repo->findAllPeopleQuery($personSearch),
            $request->query->getInt("page", 1), /*page number*/
            20 /*limit per page*/
        );

        $people->setCustomParameters([
            'align' => 'right',
        ]);

        return $this->render("app/listPeople.html.twig", [
            "controller_name" => "PersonController",
            "people" => $people,
            "personSearch" =>$personSearch,
            "form" => $form->createView(),
            "current_menu" => "list_people"
        ]);       
    }

    /**
     * @Route("/group/{id}/search/person", name="group_search_person")
     * @return Response
     */
    public function groupSearchPerson(GroupPeople $groupPeople, PaginatorInterface $paginator, Request $request, PersonSearch $personSearch = NULL): Response
    {
        if (!$personSearch) {
            $personSearch = new PersonSearch();
        } 
               
        $form = $this->createForm(PersonSearchMinType::class, $personSearch);

        $form->handleRequest($request);

        dump($personSearch);

        if (count($request->query)) {
            $people =  $paginator->paginate(
                $this->repo->findAllPeopleQuery($personSearch),
                $request->query->getInt("page", 1), /*page number*/
                20 /*limit per page*/
            );

            $people->setCustomParameters([
                'align' => 'right',
            ]);

            return $this->render("app/listPeople.html.twig", [
                "controller_name" => "PersonController",
                "group_people" => $groupPeople,
                "people" => $people,
                "personSearch" =>$personSearch,
                "form" => $form->createView(),
                "current_menu" => "list_people"
            ]);    
        } else {

            return $this->render("app/listPeople.html.twig", [
                "controller_name" => "PersonController",
                "group_people" => $groupPeople,
                "personSearch" =>$personSearch,
                "form" => $form->createView(),
                "current_menu" => "list_people"
            ]);  
        }
  
    }


        /**
     * @Route("/group/{id}/add/person/{person_id}", name="group_add_person")
     * @ParamConverter("person", options={"id" = "person_id"})
     * @return Response
     */
    public function addPersonInGroup(GroupPeople $groupPeople, Person $person, RolePerson $rolePerson = NULL, Request $request): Response
    {
        $rolePerson = new RolePerson;

        $rolePerson
            ->setHead(FALSE)
            ->setCreatedAt(new \DateTime())
            ->setGroupPeople($groupPeople)
            ->setRole(5);
        
        $this->manager->persist($rolePerson);

        $person->addRolesPerson($rolePerson);
        
        $this->manager->flush();

        $this->addFlash(
            "success",
            $person->getFirstname() . " a été ajouté".  Agree::gender($person->getGender()) . " au ménage."
        );

        return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);   
    }

    /**
     * @Route("/search/person", name="person_search")
     */
    public function personSearch(PersonSearch $personSearch = NULL, Request $request) 
    {
        $personSearch = new PersonSearch();
        
        $form = $this->createForm(PersonSearchType::class, $personSearch);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->IsValid()) {
            return $this->redirectToRoute("list_people", [
            "personSearch" => $personSearch,
            ]);   
        }

        return $this->render("app/personSearch.html.twig", [
        "personSearch" =>$personSearch,
        "form" => $form->createView(),
        "current_menu" => "person_search"
        ]);
    }

    /**
     * @Route("/group/{id}/person/new", name="create_person", methods="GET|POST")
     * @Route("/group/{id}/person/{person_id}-{slug}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     * @return Response
     */
    public function formPerson(GroupPeople $groupPeople, Person $person = NULL, RolePerson $rolePerson = NULL, Request $request): Response
    {
        if (!$person) {
            $person = new Person();
        }

        $form = $this->createForm(PersonType::class, $person);

        $form->handleRequest($request);

        // $groupPeople = $this->session->get("groupPeople");

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->security->getUser();

            if (!$person->getId()) {
                
                $person
                    ->setCreatedAt(new \DateTime())
                    ->setCreatedBy($user->getid());

                $rolePerson = new RolePerson();
                $rolePerson
                    ->setHead(FALSE)
                    ->setCreatedAt(new \DateTime())
                    ->setGroupPeople($groupPeople)
                    ->setRole(1);
                
                $this->manager->persist($rolePerson);

                $person->addRolesPerson($rolePerson);
                
                $this->addFlash(
                    "success",
                    $person->getFirstname() . " a été ajouté".  Agree::gender($person->getGender()) . " au ménage."
                );

            } else {
                $this->addFlash(
                    "success",
                    "Les modifications ont été enregistrées."
                );
            }

            $person
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user->getid());

            $this->manager->persist($person);
            $this->manager->flush();

            // return $this->redirectToRoute("group_people", ["id" => $groupPeople->getId()]);   
            return $this->redirectToRoute("person_show", [
                "id" => $groupPeople->getId(), 
                "person_id" => $person->getId(),
                "slug" => $person->getSlug()
                ]);
        }

        return $this->render("app/person.html.twig", [
            "group_people" =>$groupPeople,
            "person" => $person,
            "form" => $form->createView(),
            "edit_mode" => $person->getId() != NULL
        ]);
    }
}