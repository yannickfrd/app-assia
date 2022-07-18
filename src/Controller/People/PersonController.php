<?php

declare(strict_types=1);

namespace App\Controller\People;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Form\Admin\Security\SiSiaoLoginType;
use App\Form\Model\People\DuplicatedPeopleSearch;
use App\Form\Model\People\PersonSearch;
use App\Form\Model\SiSiao\SiSiaoLogin;
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
use App\Service\People\PeopleGroupManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PersonController extends AbstractController
{
    use ErrorMessageTrait;

    private $em;
    private $personRepo;
    private $translator;

    public function __construct(EntityManagerInterface $em, PersonRepository $personRepo, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->personRepo = $personRepo;
        $this->translator = $translator;
    }

    /**
     * @Route("/people", name="person_index", methods="GET|POST")
     * @Route("/new_support/search/person", name="new_support_search_person", methods="GET|POST")
     */
    public function index(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(PersonSearchType::class, $search = new PersonSearch())
            ->handleRequest($request);

        $siSiaoLoginForm = $this->createForm(SiSiaoLoginType::class, new SiSiaoLogin());

        return $this->render('app/people/person/person_index.html.twig', [
            'personSearch' => $search,
            'form' => $form->createView(),
            'siSiaoLoginForm' => $siSiaoLoginForm->createView(),
            'people' => $request->query->all() ? $pagination->paginate(
                $this->personRepo->findPeopleQuery($search, $request->query->get('search-person'), 20),
                $request
            ) : null,
        ]);
    }

    /**
     * Créer une nouvelle personne.
     *
     * @Route("/person/new", name="person_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $form = $this->createForm(RolePersonGroupType::class, $rolePerson = new RolePerson())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createPerson($rolePerson);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'error_occurred');
        }

        return $this->render('app/people/person/person_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/person/{id}-{slug}", name="person_show_slug", requirements={"slug" : "[a-z0-9\-]*"}, methods="GET")
     * @Route("/person/{id}", name="person_show", methods="GET")
     */
    public function show(int $id, Request $request, SupportPersonRepository $supportRepo): Response
    {
        if (null === $person = $this->personRepo->findPersonById($id)) {
            throw $this->createAccessDeniedException('Cette personne n\'existe pas.');
        }

        $form = $this->createForm(PersonType::class, $person, [])
            ->handleRequest($request);

        // Formulaire pour ajouter un nouveau groupe à la personne
        $formNewGroup = $this->createForm(PersonNewGroupType::class, new RolePerson(), [
            'action' => $this->generateUrl('person_new_group', ['id' => $person->getId()]),
        ]);

        $supports = $supportRepo->findSupportsOfPerson($person);

        return $this->render('app/people/person/person_edit.html.twig', [
            'form' => $form->createView(),
            'supports' => $supports,
            'form_new_group' => $formNewGroup->createView(),
            'canEdit' => $this->canEdit($person, $supports),
        ]);
    }

    /**
     * @Route("/person/{id}/edit", name="person_edit_ajax", methods="POST")
     */
    public function edit(Person $person, Request $request): JsonResponse
    {
        $form = $this->createForm(PersonType::class, $person)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->discacheSupport($person);

            /** @var User $user */
            $user = $this->getUser();

            return $this->json([
                'alert' => 'success',
                'msg' => $this->translator->trans('updated_successfully', [], 'app'),
                'user' => $user->getFullname(),
                'date' => $person->getUpdatedAt()->format('d/m/Y à H:i'),
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/person/{id}/delete", name="person_delete", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Person $person): Response
    {
        $this->em->remove($person);
        $this->em->flush();

        $this->addFlash('warning', 'person.deleted_successfully');

        return $this->redirectToRoute('home');
    }

    /**
     * Rechercher des personnes via requête Ajax.
     *
     * @Route("/people/search", name="people_search", methods="POST")
     */
    public function searchPeople(Request $request): JsonResponse
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
    public function addPersonToGroup(PeopleGroup $peopleGroup, Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(PersonSearchType::class, $search = new PersonSearch())
            ->handleRequest($request);

        $rolePerson = (new RolePerson())->setPeopleGroup($peopleGroup);
        $formRolePerson = $this->createForm(RolePersonType::class, $rolePerson, [
            'attr' => ['supports' => $request->get('supports')],
        ])->handleRequest($request);

        return $this->render('app/people/person/person_index.html.twig', [
            'form' => $form->createView(),
            'form_role_person' => $formRolePerson->createView() ?? null,
            'people_group' => $peopleGroup,
            'personSearch' => $search,
            'people' => $request->query->all() ? $pagination->paginate($this->personRepo->findPeopleQuery($search), $request) : null,
        ]);
    }

    /**
     * Créer une nouvelle personne dans un group existant.
     *
     * @Route("/group/{id}/person/new", name="group_create_person", methods="GET|POST")
     */
    public function newPersonInGroup(
        PeopleGroup $peopleGroup,
        Request $request,
        PeopleGroupManager $peopleGroupManager
    ): Response {
        $form = $this->createForm(PersonRolePersonType::class, $rolePerson = new RolePerson(), [
            'attr' => ['supports' => $request->get('supports')],
        ])->handleRequest($request);

        $person = $rolePerson->getPerson();

        if ($form->isSubmitted() && $form->isValid()) {
            $personExists = $this->personExists($person);
            // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
            if ($personExists) {
                $this->addFlash('warning', $this->translator->trans('person.already_exists', [
                    'person_fullname' => $person->getFullname()
                ], 'app'));

                return $this->redirectToRoute('person_show', ['id' => $personExists->getId()]);
            }

            $peopleGroupManager->createPersonInGroup($peopleGroup, $person, $rolePerson, $form->get('addPersonToSupport')->getData());

            return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'error_occurred');
        }

        return $this->render('app/people/person/person_edit.html.twig', [
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
    public function editPersonInGroup(int $id, int $person_id, PeopleGroupRepository $peopleGroupRepo,
        SupportPersonRepository $supportRepo): Response
    {
        if (null === $person = $this->personRepo->findPersonById($person_id)) {
            throw $this->createAccessDeniedException('Cette personne n\'existe pas.');
        }

        $form = $this->createForm(PersonType::class, $person);

        $formNewGroup = $this->createForm(PersonNewGroupType::class, new RolePerson(), [
            'action' => $this->generateUrl('person_new_group', ['id' => $person->getId()]),
        ]);

        $supports = $supportRepo->findSupportsOfPerson($person);

        return $this->render('app/people/person/person_edit.html.twig', [
            'form' => $form->createView(),
            'people_group' => $peopleGroupRepo->findPeopleGroupById($id),
            'supports' => $supports,
            'form_new_group' => $formNewGroup->createView(),
            'canEdit' => $this->canEdit($person, $supports),
        ]);
    }

    /**
     * Ajoute un nouveau groupe à la personne.
     *
     * @Route("/person/{id}/new_group", name="person_new_group", methods="POST")
     */
    public function addNewGroupToPerson(Person $person, Request $request): Response
    {
        $form = $this->createForm(PersonNewGroupType::class, $rolePerson = new RolePerson())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createNewGroupToPerson($person, $rolePerson);
        }
        $this->addFlash('danger', 'error_occurred');

        return $this->redirectToRoute('person_show', ['id' => $person->getId()]);
    }

    /**
     * Permet de trouver les personnes par le mode de recherche instannée AJAX.
     *
     * @Route("/search/person", name="search_person", methods="GET")
     * @Route("/search/person/{search}", name="search_person", methods="GET")
     */
    public function searchPerson(string $search): JsonResponse
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
     * @Route("/duplicated_people", name="duplicated_people_index", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function listDuplicatedPeople(Request $request): Response
    {
        $form = $this->createForm(DuplicatedPeopleType::class, $search = new DuplicatedPeopleSearch())
            ->handleRequest($request);

        return $this->render('app/people/person/duplicated_people_index.html.twig', [
            'form' => $form->createView(),
            'people' => $this->personRepo->findDuplicatedPeople($search),
        ]);
    }

    /**
     * Crée une nouvelle personne.
     */
    protected function createPerson(RolePerson $rolePerson): ?Response
    {
        $person = $rolePerson->getPerson();
        $peopleGroup = $rolePerson->getPeopleGroup();

        // Si la personne existe déjà, renvoie vers la fiche existante, sinon crée la personne
        if ($this->personExists($person)) {
            $this->addFlash('warning', $this->translator->trans('person.already_exists',[
                'person_fullname' => $person->getFullname()
            ], 'app'));

            return null;
        }

        $this->em->persist($peopleGroup);

        $rolePerson->setHead(true)
            ->setPeopleGroup($peopleGroup);

        $this->em->persist($rolePerson);

        $person->addRolesPerson($rolePerson);

        $this->em->persist($person);

        $this->em->flush();

        $this->addFlash('success', $this->translator->trans('person.created_successfully', [
            'person_fullname' => $person->getFullname(),
            'e' => Grammar::gender($person->getGender()),
        ], 'app'));

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
     * Crée un nouveau groupe à la personne.
     */
    protected function createNewGroupToPerson(Person $person, RolePerson $rolePerson): Response
    {
        $peopleGroup = $rolePerson->getPeopleGroup();

        $this->em->persist($peopleGroup);

        $rolePerson->setHead(true)
            ->setPeopleGroup($peopleGroup);

        $this->em->persist($rolePerson);

        $person->addRolesPerson($rolePerson);

        $this->em->persist($person);

        $this->em->flush();

        $this->addFlash('success', 'people_group.created_successfully');

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Vérifie si l'utilisateur a les droits concernant la personne.
     */
    protected function canEdit(Person $person, array $supports): bool
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN') || $person->getCreatedBy() === $this->getUser() || 0 === count($supports)) {
            return true;
        }

        /** @var User $user */
        $user = $this->getUser();

        foreach ($supports as $supportPerson) {
            if ($user->hasService($supportPerson->getSupportGroup()->getService())) {
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
