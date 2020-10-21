<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\EvaluationGroup;
use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\SupportGroup;
use App\Form\Model\DuplicatedPeopleSearch;
use App\Form\Model\PersonSearch;
use App\Form\Person\DuplicatedPeopleType;
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
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class PersonController extends AbstractController
{
    use ErrorMessageTrait;

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
     */
    public function listPeople(Request $request, Pagination $pagination): Response
    {
        $search = new PersonSearch();

        $form = ($this->createForm(PersonSearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/person/listPeople.html.twig', [
            'personSearch' => $search,
            'form' => $form->createView(),
            'people' => $request->query->all() ? $pagination->paginate($this->repo->findAllPeopleQuery($search, $request->query->get('search-person'), 20), $request) : null,
        ]);
    }

    /**
     * Permet de trouver les personnes par le mode de recherche instannée AJAX.
     *
     * @Route("/people/search", name="people_search", methods="POST")
     */
    public function searchPeople(Request $request): Response
    {
        $search = new PersonSearch();

        $this->createForm(PersonSearchType::class, $search)
            ->handleRequest($request);

        $people = [];

        foreach ($this->repo->findAllPeopleQuery($search, null, 20)->getResult() as $person) {
            $people[] = [
                'id' => $person->getId(),
                'lastname' => $person->getLastname(),
                'usename' => $person->getUsename(),
                'firstname' => $person->getFirstname(),
                'birthdate' => $person->getBirthdate()->format('d/m/Y'),
                'age' => $person->getAge(),
                'gender' => $person->getGender(),
            ];
        }

        return $this->json([
                'search' => $search,
                // 'count' => $count,
                'people' => $people,
        ], 200);
    }

    /**
     * Rechercher une personne pour l'ajouter dans un groupe.
     *
     * @Route("/group/{id}/search_person", name="group_search_person", methods="GET|POST")
     */
    public function addPersonInGroup(GroupPeople $groupPeople, Request $request, Pagination $pagination): Response
    {
        $search = new PersonSearch();

        $form = ($this->createForm(PersonSearchType::class, $search))
            ->handleRequest($request);

        $formRolePerson = ($this->createForm(RolePersonType::class, new RolePerson()))
            ->handleRequest($request);

        return $this->render('app/person/listPeople.html.twig', [
            'form' => $form->createView(),
            'form_role_person' => $formRolePerson->createView() ?? null,
            'group_people' => $groupPeople,
            'personSearch' => $search,
            'people' => $request->query->all() ? $pagination->paginate($this->repo->findAllPeopleQuery($search), $request) : null,
        ]);
    }

    /**
     * Nouvelle personne.
     *
     * @Route("/person/new", name="person_new", methods="GET|POST")
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
        ]);
    }

    /**
     * Crée une nouvelle personne dans un group existant.
     *
     * @Route("/group/{id}/person/new", name="group_create_person", methods="GET|POST")
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
        ]);
    }

    /**
     * Modification d'une personne.
     *
     * @Route("/group/{id}/person/{person_id}-{slug}", name="group_person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET|POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function editPersonInGroup(GroupPeople $groupPeople, int $person_id, Request $request, SessionInterface $session): Response
    {
        $person = $this->repo->findPersonById($person_id);

        $form = ($this->createForm(PersonType::class, $person))
            ->handleRequest($request);

        $formNewGroup = $this->createForm(PersonNewGroupType::class, new RolePerson(), [
            'action' => $this->generateUrl('person_new_group', ['id' => $person->getId()]),
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();
        }

        return $this->render('app/person/person.html.twig', [
            'group_people' => $groupPeople,
            'form' => $form->createView(),
            'form_new_group' => $formNewGroup->createView(),
            'hasRights' => $this->hasRights($person, $session),
        ]);
    }

    /**
     * Voir la fiche individuelle.
     *
     * @Route("/person/{id}-{slug}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET")
     * @Route("/person/{id}", name="person_show", methods="GET")
     */
    public function personShow(Person $person, RolePerson $rolePerson = null, Request $request, SessionInterface $session): Response
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
            'hasRights' => $this->hasRights($person, $session),
        ]);
    }

    /**
     * Met à jour les informations d'une personne via Ajax.
     *
     * @Route("/person/{id}/edit", name="person_edit_ajax", methods="POST")
     */
    public function editPerson(Person $person, Request $request): Response
    {
        $form = ($this->createForm(PersonType::class, $person))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updatePerson($person);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Ajoute un nouveau groupe à la personne.
     *
     * @Route("/person/{id}/new_group", name="person_new_group", methods="POST")
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

        $this->addFlash('warning', 'La personne est supprimée.');

        return $this->redirectToRoute('people');
    }

    /**
     * Permet de trouver les personnes par le mode de recherche instannée AJAX.
     *
     * @Route("/search/person", name="search_person", methods="GET")
     */
    public function searchPerson(Request $request): Response
    {
        $people = [];

        foreach ($this->repo->findPeopleByResearch($request->query->get('search')) as $person) {
            $people[] = [
                'id' => $person->getId(),
                'fullname' => $person->getFullname(),
            ];
        }

        return $this->json([
                'people' => $people,
            ], 200);
    }

    /**
     * Liste des personnes en doublons.
     *
     * @Route("/duplicated_people", name="duplicated_people", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function listDuplicatedPeople(Request $request): Response
    {
        $search = new DuplicatedPeopleSearch();

        $form = ($this->createForm(DuplicatedPeopleType::class, $search))
            ->handleRequest($request);

        return $this->render('app/person/listDuplicatedPeople.html.twig', [
            'form' => $form->createView(),
            'people' => $this->repo->findDuplicatedPeople($search),
        ]);
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
            $this->addFlash('warning', 'Attention : '.$person->getFullname().' existe déjà !');

            return;
        }

        $this->manager->persist($groupPeople);

        $rolePerson->setHead(true)
            ->setGroupPeople($groupPeople);

        $this->manager->persist($rolePerson);

        $person->addRolesPerson($rolePerson);

        $this->manager->persist($person);

        $this->manager->flush();

        $this->addFlash('success', $person->getFullname().' est créé'.Grammar::gender($person->getGender()).', ainsi que son groupe.');

        return $this->redirectToRoute('group_people_show', ['id' => $groupPeople->getId()]);
    }

    /**
     * Vérifie si la personne existe déjà.
     */
    protected function personExists(Person $person): ?Person
    {
        return $this->repo->findOneBy([
            'lastname' => $person->getLastname(),
            'firstname' => $person->getFirstname(),
            'birthdate' => $person->getBirthdate(),
        ]);
    }

    /**
     * Crée une personne avec son rôle.
     */
    protected function createPersonInGroup(Person $person, RolePerson $rolePerson = null, GroupPeople $groupPeople)
    {
        $rolePerson->setHead(false)
            ->setGroupPeople($groupPeople);
        $this->manager->persist($rolePerson);

        $person->addRolesPerson($rolePerson);
        $this->manager->persist($person);

        $groupPeople->setNbPeople($groupPeople->getRolePeople()->count() + 1);

        $this->manager->flush();

        $this->addFlash('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');
    }

    /**
     * Met à jour la personne.
     */
    protected function updatePerson(Person $person): Response
    {
        $this->manager->flush();

        $this->discacheSupport($person);

        return $this->json([
            'code' => 200,
            'alert' => 'success',
            'msg' => 'Les modifications sont enregistrées.',
            'user' => $this->getUser()->getFullname(),
            'date' => $person->getUpdatedAt()->format('d/m/Y à H:i'),
        ], 200);
    }

    /**
     * Crée un nouveau groupe à la personne.
     */
    protected function createNewGroupToPerson(Person $person, RolePerson $rolePerson)
    {
        $groupPeople = $rolePerson->getGroupPeople();

        $this->manager->persist($groupPeople);

        $rolePerson->setHead(true)
            ->setGroupPeople($groupPeople);

        $this->manager->persist($rolePerson);

        $person->addRolesPerson($rolePerson);

        $this->manager->persist($person);

        $this->manager->flush();

        $this->addFlash('success', 'Le nouveau groupe est créé.');

        return $this->redirectToRoute('group_people_show', ['id' => $groupPeople->getId()]);
    }

    /**
     * Vérifie si l'utilisateur a les droits concernant la personne.
     */
    protected function hasRights(Person $person, SessionInterface $session): bool
    {
        if ($person->getCreatedBy() == $this->getUser()) {
            return true;
        }

        foreach ($person->getSupports() as $supportPerson) {
            if (in_array($supportPerson->getSupportGroup()->getService()->getId(), array_keys($session->get('userServices')))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Supprime le cache du suivi.
     */
    protected function discacheSupport(Person $person): void
    {
        $cache = new FilesystemAdapter();

        foreach ($person->getSupports() as $supportPerson) {
            $id = $supportPerson->getSupportGroup()->getId();
            $cache->deleteItems([
                SupportGroup::CACHE_SUPPORT_KEY.$id,
                SupportGroup::CACHE_FULLSUPPORT_KEY.$id,
                EvaluationGroup::CACHE_EVALUATION_KEY.$id,
            ]);
        }
    }
}
