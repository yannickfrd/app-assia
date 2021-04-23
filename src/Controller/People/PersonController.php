<?php

namespace App\Controller\People;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Form\Model\People\DuplicatedPeopleSearch;
use App\Form\Model\People\PersonSearch;
use App\Form\People\Person\DuplicatedPeopleType;
use App\Form\People\Person\PersonNewGroupType;
use App\Form\People\Person\PersonRolePersonType;
use App\Form\People\Person\PersonSearchType;
use App\Form\People\Person\PersonType;
use App\Form\People\Person\RolePersonGroupType;
use App\Form\People\RolePerson\RolePersonType;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\People\PersonRepository;
use App\Repository\Support\SupportPersonRepository;
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
    private $personRepo;

    public function __construct(EntityManagerInterface $manager, PersonRepository $personRepo)
    {
        $this->manager = $manager;
        $this->personRepo = $personRepo;
    }

    /**
     * Voir la liste des personnes.
     *
     * @Route("/people", name="people", methods="GET|POST")
     * @Route("/new_support/search/person", name="new_support_search_person", methods="GET|POST")
     */
    public function listPeople(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(PersonSearchType::class, $search = new PersonSearch())
            ->handleRequest($request);

        return $this->render('app/people/person/listPeople.html.twig', [
            'personSearch' => $search,
            'form' => $form->createView(),
            'people' => $request->query->all() ? $pagination->paginate($this->personRepo->findPeopleQuery($search, $request->query->get('search-person'), 20), $request) : null,
        ]);
    }

    /**
     * Rechercher des personnes via requête Ajax.
     *
     * @Route("/people/search", name="people_search", methods="POST")
     */
    public function searchPeople(Request $request): Response
    {
        $this->createForm(PersonSearchType::class, $search = new PersonSearch())
            ->handleRequest($request);

        $people = [];
        foreach ($this->personRepo->findPeopleQuery($search)->getResult() as $person) {
            $people[] = [
                'id' => $person->getId(),
                'lastname' => $person->getLastname(),
                'usename' => $person->getUsename(),
                'firstname' => $person->getFirstname(),
                'birthdate' => $person->getBirthdate()->format('d/m/Y'),
                'age' => (string) $person->getAge(),
                'gender' => $person->getGender(),
            ];
        }

        return $this->json([
                'search' => $search,
                'people' => $people,
        ]);
    }

    /**
     * Rechercher une personne pour l'ajouter dans un groupe.
     *
     * @Route("/group/{id}/search_person", name="group_search_person", methods="GET|POST")
     */
    public function addPersonInGroup(PeopleGroup $peopleGroup, Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(PersonSearchType::class, $search = new PersonSearch())
            ->handleRequest($request);

        $formRolePerson = $this->createForm(RolePersonType::class, new RolePerson())
            ->handleRequest($request);

        return $this->render('app/people/person/listPeople.html.twig', [
            'form' => $form->createView(),
            'form_role_person' => $formRolePerson->createView() ?? null,
            'people_group' => $peopleGroup,
            'personSearch' => $search,
            'people' => $request->query->all() ? $pagination->paginate($this->personRepo->findPeopleQuery($search), $request) : null,
        ]);
    }

    /**
     * Créer une nouvelle personne.
     *
     * @Route("/person/new", name="person_new", methods="GET|POST")
     */
    public function newPerson(Request $request): Response
    {
        $form = $this->createForm(RolePersonGroupType::class, $rolePerson = new RolePerson())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createPerson($rolePerson);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Attention, une erreur s'est produite.");
        }

        return $this->render('app/people/person/person.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Créer une nouvelle personne dans un group existant.
     *
     * @Route("/group/{id}/person/new", name="group_create_person", methods="GET|POST")
     */
    public function newPersonInGroup(PeopleGroup $peopleGroup, Request $request): Response
    {
        $form = $this->createForm(PersonRolePersonType::class, $rolePerson = new RolePerson())
            ->handleRequest($request);

        $person = $rolePerson->getPerson();

        if ($form->isSubmitted() && $form->isValid()) {
            $personExists = $this->personExists($person);
            // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
            if ($personExists) {
                $this->addFlash('warning', 'Attention : '.$person->getFullname().' existe déjà !');

                return $this->redirectToRoute('person_show', ['id' => $personExists->getId()]);
            }
            $this->createPersonInGroup($person, $rolePerson, $peopleGroup);

            return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Attention, une erreur s'est produite.");
        }

        return $this->render('app/people/person/person.html.twig', [
            'people_group' => $peopleGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Mettre à jour une personne.
     *
     * @Route("/group/{id}/person/{person_id}-{slug}", name="group_person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET")
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function editPersonInGroup(int $id, int $person_id, PeopleGroupRepository $peopleGroupRepo, SupportPersonRepository $supportRepo, SessionInterface $session): Response
    {
        $peopleGroup = $peopleGroupRepo->findPeopleGroupById($id);
        $person = $this->personRepo->findPersonById($person_id);

        $form = $this->createForm(PersonType::class, $person);

        $formNewGroup = $this->createForm(PersonNewGroupType::class, new RolePerson(), [
            'action' => $this->generateUrl('person_new_group', ['id' => $person->getId()]),
        ]);

        $supports = $supportRepo->findSupportsOfPerson($person);

        return $this->render('app/people/person/person.html.twig', [
            'form' => $form->createView(),
            'people_group' => $peopleGroup,
            'supports' => $supports,
            'form_new_group' => $formNewGroup->createView(),
            'canEdit' => $this->canEdit($person, $supports, $session),
        ]);
    }

    /**
     * Voir la fiche individuelle.
     *
     * @Route("/person/{id}-{slug}", name="person_show", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET")
     * @Route("/person/{id}", name="person_show", methods="GET")
     */
    public function showPerson(int $id, Request $request, SupportPersonRepository $supportRepo, SessionInterface $session): Response
    {
        $person = $this->personRepo->findPersonById($id);

        $form = $this->createForm(PersonType::class, $person)
            ->handleRequest($request);

        // Formulaire pour ajouter un nouveau groupe à la personne
        $formNewGroup = $this->createForm(PersonNewGroupType::class, new RolePerson(), [
            'action' => $this->generateUrl('person_new_group', ['id' => $person->getId()]),
        ]);

        $supports = $supportRepo->findSupportsOfPerson($person);

        return $this->render('app/people/person/person.html.twig', [
            'form' => $form->createView(),
            'supports' => $supports,
            'form_new_group' => $formNewGroup->createView(),
            'canEdit' => $this->canEdit($person, $supports, $session),
        ]);
    }

    /**
     * Met à jour les informations d'une personne via Ajax.
     *
     * @Route("/person/{id}/edit", name="person_edit_ajax", methods="POST")
     */
    public function editPerson(Person $person, Request $request): Response
    {
        $form = $this->createForm(PersonType::class, $person)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->discacheSupport($person);

            return $this->json([
                'alert' => 'success',
                'msg' => 'Les modifications sont enregistrées.',
                'user' => $this->getUser()->getFullname(),
                'date' => $person->getUpdatedAt()->format('d/m/Y à H:i'),
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Ajoute un nouveau groupe à la personne.
     *
     * @Route("/person/{id}/new_group", name="person_new_group", methods="POST")
     */
    public function addNewGroupToPerson(Person $person, Request $request)
    {
        $form = $this->createForm(PersonNewGroupType::class, $rolePerson = new RolePerson())
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

        return $this->redirectToRoute('home');
    }

    /**
     * Permet de trouver les personnes par le mode de recherche instannée AJAX.
     *
     * @Route("/search/person", name="search_person", methods="GET")
     * @Route("/search/person/{search}", name="search_person", methods="GET")
     */
    public function searchPerson(string $search): Response
    {
        $people = [];
        foreach ($this->personRepo->findPeopleByResearch($search) as $person) {
            $people[] = [
                'id' => $person->getId(),
                'fullname' => $person->getFullname(),
                'birthdate' => $person->getBirthdate()->format('d/m/Y'),
            ];
        }

        return $this->json(['people' => $people]);
    }

    /**
     * Liste des personnes en doublons.
     *
     * @Route("/duplicated_people", name="duplicated_people", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function listDuplicatedPeople(Request $request): Response
    {
        $form = $this->createForm(DuplicatedPeopleType::class, $search = new DuplicatedPeopleSearch())
            ->handleRequest($request);

        return $this->render('app/people/person/listDuplicatedPeople.html.twig', [
            'form' => $form->createView(),
            'people' => $this->personRepo->findDuplicatedPeople($search),
        ]);
    }

    /**
     * Crée une nouvelle personne.
     */
    protected function createPerson(RolePerson $rolePerson)
    {
        $person = $rolePerson->getPerson();
        $peopleGroup = $rolePerson->getPeopleGroup();

        // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
        if ($this->personExists($person)) {
            $this->addFlash('warning', 'Attention : '.$person->getFullname().' existe déjà !');

            return;
        }

        $this->manager->persist($peopleGroup);

        $rolePerson->setHead(true)
            ->setPeopleGroup($peopleGroup);

        $this->manager->persist($rolePerson);

        $person->addRolesPerson($rolePerson);

        $this->manager->persist($person);

        $this->manager->flush();

        $this->addFlash('success', $person->getFullname().' est créé'.Grammar::gender($person->getGender()).', ainsi que son groupe.');

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Vérifie si la personne existe déjà.
     */
    protected function personExists(Person $person): ?Person
    {
        return $this->personRepo->findOneBy([
            'lastname' => $person->getLastname(),
            'firstname' => $person->getFirstname(),
            'birthdate' => $person->getBirthdate(),
        ]);
    }

    /**
     * Crée une personne avec son rôle.
     */
    protected function createPersonInGroup(Person $person, RolePerson $rolePerson = null, PeopleGroup $peopleGroup)
    {
        $rolePerson->setHead(false)
            ->setPeopleGroup($peopleGroup);
        $this->manager->persist($rolePerson);

        $person->addRolesPerson($rolePerson);
        $this->manager->persist($person);

        $peopleGroup->setNbPeople(count($peopleGroup->getRolePeople()) + 1);

        $this->manager->flush();

        $this->addFlash('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');
    }

    /**
     * Crée un nouveau groupe à la personne.
     */
    protected function createNewGroupToPerson(Person $person, RolePerson $rolePerson)
    {
        $peopleGroup = $rolePerson->getPeopleGroup();

        $this->manager->persist($peopleGroup);

        $rolePerson->setHead(true)
            ->setPeopleGroup($peopleGroup);

        $this->manager->persist($rolePerson);

        $person->addRolesPerson($rolePerson);

        $this->manager->persist($person);

        $this->manager->flush();

        $this->addFlash('success', 'Le nouveau groupe est créé.');

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Vérifie si l'utilisateur a les droits concernant la personne.
     */
    protected function canEdit(Person $person, array $supports, SessionInterface $session): bool
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN') || $person->getCreatedBy() === $this->getUser()) {
            return true;
        }

        foreach ($supports as $supportPerson) {
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
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

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
