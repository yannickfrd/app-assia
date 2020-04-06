<?php

namespace App\Controller;

use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Export\PersonExport;
use App\Form\Model\PersonSearch;
use App\Form\Person\PersonNewGroupType;
use App\Form\Person\PersonRolePersonType;
use App\Form\Person\PersonSearchType;
use App\Form\Person\PersonType;
use App\Form\Person\RolePersonGroupType;
use App\Form\RolePerson\RolePersonType;
use App\Repository\PersonRepository;
use App\Service\Grammar;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * Liste des personnes.
     *
     * @Route("/people", name="people", methods="GET|POST")
     * @Route("/new_support/search/person", name="new_support_search_person", methods="GET|POST")
     *
     * @param PersonSearch $personSearch
     */
    public function listPeople(Request $request, PersonSearch $personSearch = null, Pagination $pagination): Response
    {
        $personSearch = new PersonSearch();

        $form = ($this->createForm(PersonSearchType::class, $personSearch))
            ->handleRequest($request);

        if ($personSearch->getExport()) {
            return $this->exportData($personSearch);
        }

        return $this->render('app/person/listPeople.html.twig', [
            'personSearch' => $personSearch,
            'form' => $form->createView(),
            'people' => $request->query->all() ? $pagination->paginate($this->repo->findAllPeopleQuery($personSearch, $request->query->get('search-person')), $request) : null,
        ]);
    }

    /**
     * Rechercher une personne pour l'ajouter dans un groupe.
     *
     * @Route("/group/{id}/search_person", name="group_search_person", methods="GET|POST")
     *
     * @param PersonSearch $personSearch
     */
    public function addPersonInGroup(GroupPeople $groupPeople, PersonSearch $personSearch = null, Request $request, Pagination $pagination): Response
    {
        $personSearch = new PersonSearch();

        $form = ($this->createForm(PersonSearchType::class, $personSearch))
            ->handleRequest($request);

        $formRolePerson = ($this->createForm(RolePersonType::class, new RolePerson()))
            ->handleRequest($request);

        return $this->render('app/person/listPeople.html.twig', [
            'form' => $form->createView(),
            'form_role_person' => $formRolePerson->createView() ?? null,
            'group_people' => $groupPeople,
            'personSearch' => $personSearch,
            'people' => $request->query->all() ? $pagination->paginate($this->repo->findAllPeopleQuery($personSearch), $request) : null,
        ]);
    }

    /**
     * Nouvelle personne.
     *
     * @Route("/person/new", name="person_new", methods="GET|POST")
     *
     * @param RolePerson $rolePersonequest
     */
    public function newPerson(RolePerson $rolePerson = null, Request $request): Response
    {
        $rolePerson = new RolePerson();

        $form = ($this->createForm(RolePersonGroupType::class, $rolePerson))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createPerson($rolePerson);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Attention, une erreur s'est produite.");
        }

        return $this->render('app/person/person.html.twig', [
            'form' => $form->createView(),
            'edit_mode' => false,
        ]);
    }

    /**
     * Crée une nouvelle personne dans un group existant.
     *
     * @Route("/group/{id}/person/new", name="group_create_person", methods="GET|POST")
     *
     * @param RolePerson $rolePerson
     */
    public function newPersonInGroup(GroupPeople $groupPeople, RolePerson $rolePerson = null, Request $request): Response
    {
        $rolePerson = new RolePerson();

        $form = ($this->createForm(PersonRolePersonType::class, $rolePerson))
            ->handleRequest($request);

        $person = $rolePerson->getPerson();

        if ($form->isSubmitted() && $form->isValid()) {
            $personExists = $this->personExists($person);
            // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
            if ($personExists) {
                $this->addFlash('warning', 'Attention : '.$person->getFullname().' existe déjà !');

                return $this->redirectToRoute('person_show', ['id' => $personExists->getId()]);
            }
            $this->createPersonInGroup($person, $rolePerson, $groupPeople);

            return $this->redirectToRoute('group_people_show', ['id' => $groupPeople->getId()]);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Attention, une erreur s'est produite.");
        }

        return $this->render('app/person/person.html.twig', [
            'group_people' => $groupPeople,
            'form' => $form->createView(),
            'edit_mode' => false,
        ]);
    }

    /**
     * Modification d'une personne.
     *
     * @Route("/group/{id}/person/{person_id}-{slug}", name="group_person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function editPersonInGroup(GroupPeople $groupPeople, int $person_id, Request $request): Response
    {
        $person = $this->repo->findPersonById($person_id);

        $form = ($this->createForm(PersonType::class, $person))
            ->handleRequest($request);

        $formNewGroup = $this->createForm(PersonNewGroupType::class, new RolePerson(), [
            'action' => $this->generateUrl('person_new_group', ['id' => $person->getId()]),
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $person->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->getUser());
            $this->manager->flush();
        }

        return $this->render('app/person/person.html.twig', [
            'group_people' => $groupPeople,
            'form' => $form->createView(),
            'form_new_group' => $formNewGroup->createView(),
            'edit_mode' => true,
        ]);
    }

    /**
     * Voir la fiche individuelle.
     *
     * @Route("/person/{id}-{slug}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET")
     * @Route("/person/{id}", name="person_show", methods="GET")
     *
     * @param RolePerson $rolePerson
     */
    public function personShow(Person $person, RolePerson $rolePerson = null, Request $request): Response
    {
        $form = ($this->createForm(PersonType::class, $person))
            ->handleRequest($request);

        // Formulaire pour ajouter un nouveau groupe à la personne
        $rolePerson = new RolePerson();
        $formNewGroup = $this->createForm(PersonNewGroupType::class, $rolePerson, [
            'action' => $this->generateUrl('person_new_group', ['id' => $person->getId()]),
        ]);

        return $this->render('app/person/person.html.twig', [
            'form' => $form->createView(),
            'form_new_group' => $formNewGroup->createView(),
            'edit_mode' => true,
        ]);
    }

    /**
     * Met à jour les informations d'une personne via Ajax.
     *
     * @Route("/person/{id}/edit", name="person_edit_ajax", methods="POST")
     */
    public function editPerson(Person $person, Request $request, ValidatorInterface $validator): Response
    {
        $form = ($this->createForm(PersonType::class, $person))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updatePerson($person);
        }

        return $this->errorMessage($validator, $form);
    }

    /**
     * Ajoute un nouveau groupe à la personne.
     *
     * @Route("/person/{id}/new_group", name="person_new_group", methods="POST")
     *
     * @param RolePerson $rolePerson
     */
    public function addNewGroupToPerson(Person $person, RolePerson $rolePerson = null, Request $request)
    {
        $rolePerson = new RolePerson();

        $form = ($this->createForm(PersonNewGroupType::class, $rolePerson))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createNewGroupToPerson($person, $rolePerson);
        }
        $this->addFlash('danger', "Une erreur s'est produite");

        return $this->redirectToRoute('person_show', ['id' => $person->getId()]);
    }

    /**
     * Supprime la personne.
     *
     * @Route("/person/{id}/delete", name="person_delete", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deletePerson(Person $person): Response
    {
        $this->manager->remove($person);
        $this->manager->flush();

        $this->addFlash('warning', 'La personne a été supprimée.');

        return $this->redirectToRoute('people');
    }

    /**
     * Permet de trouver les personnes par le mode de recherche instannée AJAX.
     *
     * @Route("/search/person", name="search_person", methods="GET")
     *
     * @param Person $person
     */
    public function searchPerson(Request $request): Response
    {
        $search = $request->query->get('search') ?? null;

        $people = $this->repo->findPeopleByResearch($search);
        $nbResults = count($people);

        if ($nbResults) {
            foreach ($people as $person) {
                $results[] = [
                    'id' => $person->getId(),
                    'fullname' => $person->getFullname(),
                ];
            }

            return $this->json([
                'nb_results' => $nbResults,
                'results' => $results,
            ], 200);
        }

        return $this->json([
            'nb_results' => $nbResults,
            'results' => 'Aucun résultat.',
        ], 200);
    }

    /**
     * Export des données.
     */
    protected function exportData(PersonSearch $personSearch)
    {
        $people = $this->repo->findPeopleToExport($personSearch);

        return (new PersonExport())->exportData($people);
    }

    /**
     * Crée une nouvelle personne.
     */
    protected function createPerson(RolePerson $rolePerson)
    {
        $person = $rolePerson->getPerson();
        $groupPeople = $rolePerson->getGroupPeople();

        // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
        if ($this->personExists($person)) {
            return $this->addFlash('warning', 'Attention : '.$person->getFullname().' existe déjà !');
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

        $this->addFlash('success', $person->getFullname().' a été créé'.Grammar::gender($person->getGender()).', ainsi que son groupe.');

        return $this->redirectToRoute('group_people_show', ['id' => $groupPeople->getId()]);
    }

    /**
     * Vérifie si la personne existe déjà.
     *
     * @return Person|null
     */
    protected function personExists(Person $person)
    {
        return $this->repo->findOneBy([
            'lastname' => $person->getLastname(),
            'firstname' => $person->getFirstname(),
            'birthdate' => $person->getBirthdate(),
        ]);
    }

    /**
     * Crée une personne avec son rôle.
     *
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

        $this->addFlash('success', $person->getFullname().' a été ajouté'.Grammar::gender($person->getGender()).' au groupe.');
    }

    /**
     * Met à jour la personne.
     */
    protected function updatePerson(Person $person): Response
    {
        $now = new \DateTime();

        $person->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'alert' => 'success',
            'msg' => 'Les modifications ont été enregistrées.',
            'user' => $this->getUser()->getFullname(),
            'date' => date_format($now, 'd/m/Y à H:i'),
        ], 200);
    }

    /**
     * Crée un nouveau groupe à la personne.
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

        $this->addFlash('success', 'Le nouveau groupe a été créé.');

        return $this->redirectToRoute('group_people_show', ['id' => $groupPeople->getId()]);
    }

    /**
     * Retourne un message d'erreur au format JSON.
     *
     * @param ValidatorInterface $validator
     */
    protected function errorMessage(ValidatorInterface $validator = null, $form): Response
    {
        $errors = $validator->validate($form);
        foreach ($errors as $error) {
            $msg[] = $error->getMessage();
        }

        return $this->json([
            'code' => 403,
            'alert' => 'danger',
            'msg' => "Une erreur s'est produite : ".join($msg, ' '),
        ], 200);
    }
}
