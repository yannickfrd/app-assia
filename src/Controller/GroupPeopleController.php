<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\EvaluationGroup;
use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Entity\SupportGroup;
use App\Form\GroupPeople\GroupPeopleSearchType;
use App\Form\GroupPeople\GroupPeopleType;
use App\Form\Model\GroupPeopleSearch;
use App\Form\RolePerson\RolePersonType;
use App\Repository\GroupPeopleRepository;
use App\Repository\ReferentRepository;
use App\Repository\RolePersonRepository;
use App\Repository\SupportGroupRepository;
use App\Service\Grammar;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupPeopleController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, GroupPeopleRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des groupes de personnes.
     *
     * @Route("/groups_people", name="groups_people", methods="GET|POST")
     */
    public function listGroupsPeople(Request $request, Pagination $pagination): Response
    {
        $search = new GroupPeopleSearch();

        $form = ($this->createForm(GroupPeopleSearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/groupPeople/listGroupsPeople.html.twig', [
            'form' => $form->createView(),
            'groupsPeople' => $pagination->paginate($this->repo->findAllGroupPeopleQuery($search), $request),
        ]);
    }

    /**
     * Modification d'un groupe.
     *
     * @Route("/group/{id}", name="group_people_show", methods="GET|POST")
     */
    public function showGroupPeople(int $id, Request $request, ReferentRepository $repoReferent, SupportGroupRepository $repoSuppport): Response
    {
        $groupPeople = $this->repo->findGroupPeopleById($id);

        $formGroupPeople = $this->createForm(GroupPeopleType::class, $groupPeople);
        $formGroupPeople->handleRequest($request);

        $supports = $this->getSupports($groupPeople, $repoSuppport);

        if ($formGroupPeople->isSubmitted() && $formGroupPeople->isValid()) {
            $this->updateGroupPeople($groupPeople, $supports);
        }

        return $this->render('app/groupPeople/groupPeople.html.twig', [
            'form' => $formGroupPeople->createView(),
            'supports' => $supports,
            'referents' => $this->getReferents($groupPeople, $repoReferent),
        ]);
    }

    /**
     * Supprime le groupe de personnes.
     *
     * @Route("/group/{id}/delete", name="group_people_delete", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deleteGroupPeople(GroupPeople $groupPeople): Response
    {
        $this->manager->remove($groupPeople);
        $this->manager->flush();

        $this->addFlash('warning', 'Le groupe est supprimé.');

        return $this->redirectToRoute('home');
    }

    /**
     * Ajout d'une personne dans un groupe.
     *
     * @Route("/group/{id}/add/person/{person_id}", name="group_add_person", methods="POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function tryAddPersonInGroup(int $id, Person $person, RolePerson $rolePerson = null, RolePersonRepository $repoRolePerson, Request $request): Response
    {
        $groupPeople = $this->repo->findGroupPeopleById($id);

        $rolePerson = new RolePerson();

        $form = ($this->createForm(RolePersonType::class, $rolePerson))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addPersonInGroup($groupPeople, $rolePerson, $person, $repoRolePerson);
        } else {
            $this->addFlash('danger', "Une erreur s'est produite.");
        }

        return $this->redirectToRoute('group_people_show', ['id' => $groupPeople->getId()]);
    }

    /**
     * Retire la personne du groupe.
     *
     * @Route("/role_person/{id}/remove/{_token}", name="role_person_remove", methods="GET")
     */
    public function tryRemovePersonInGroup(RolePerson $rolePerson, Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->accessDenied();
        }
        // Vérifie si le token est valide avant de retirer la personne du groupe
        if ($this->isCsrfTokenValid('remove'.$rolePerson->getId(), $request->get('_token'))) {
            return $this->removePersonInGroup($rolePerson);
        }

        return $this->getErrorMessage();
    }

    /**
     * Met à jour un groupe de personnes.
     */
    protected function updateGroupPeople(GroupPeople $groupPeople, array $supports)
    {
        $this->manager->flush();

        $this->discacheSupports($supports);

        $this->addFlash('success', 'Les modifications sont enregistrées.');
    }

    /**
     * Ajoute une personne dans le groupe.
     */
    protected function addPersonInGroup(GroupPeople $groupPeople, RolePerson $rolePerson, person $person, RolePersonRepository $repoRolePerson)
    {
        // Si la personne est asssociée, ne fait rien, créé la liaison
        if ($this->personExists($groupPeople, $person, $repoRolePerson)) {
            $this->addFlash('warning', $person->getFullname().' est déjà associé'.Grammar::gender($person->getGender()).' au groupe.');

            return;
        }

        $rolePerson
            ->setHead(false)
            ->setGroupPeople($groupPeople);

        $person->addRolesPerson($rolePerson);

        $this->manager->persist($rolePerson);

        $groupPeople->setNbPeople($groupPeople->getRolePeople()->count() + 1); // Compte le nombre de personnes dans le groupe et ajoute 1

        $this->manager->flush();

        $this->addFlash('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' au groupe.');

        return;
    }

    /**
     *  Vérifie si la personne est déjà rattachée à ce groupe.
     */
    protected function personExists(GroupPeople $groupPeople, Person $person, RolePersonRepository $repoRolePerson): ?RolePerson
    {
        return $repoRolePerson->findOneBy([
            'person' => $person->getId(),
            'groupPeople' => $groupPeople->getId(),
        ]);
    }

    /**
     * Retire une personne d'un groupe.
     */
    protected function removePersonInGroup(RolePerson $rolePerson): Response
    {
        $person = $rolePerson->getPerson();
        $groupPeople = $rolePerson->getGroupPeople();
        $nbPeople = $groupPeople->getRolePeople()->count(); // // Compte le nombre de personnes dans le groupe

        // Vérifie si la personne est le demandeur principal
        if ($rolePerson->getHead()) {
            return $this->json([
                'code' => 200,
                'action' => 'error',
                'alert' => 'danger',
                'msg' => 'Le demandeur principal ne peut pas être retiré du groupe.',
                'data' => null,
            ], 200);
        }

        $groupPeople->removeRolePerson($rolePerson);
        $groupPeople->setNbPeople($nbPeople - 1);

        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $person->getFullname().' est retiré'.Grammar::gender($person->getGender()).' du groupe.',
            'data' => $nbPeople - 1,
        ], 200);
    }

    /**
     * Retourne un message d'accès refusé.
     */
    protected function accessDenied(): Response
    {
        return $this->json([
            'code' => 403,
            'alert' => 'danger',
            'msg' => "Vous n'avez pas les droits pour cette action. Demandez à un administrateur de votre service.",
        ], 200);
    }

    protected function getSupports(GroupPeople $groupPeople, SupportGroupRepository $repoSuppport)
    {
        return (new FilesystemAdapter())->get(GroupPeople::CACHE_GROUP_SUPPORTS_KEY.$groupPeople->getId(), function (CacheItemInterface $item) use ($groupPeople, $repoSuppport) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoSuppport->findSupportsOfGroupPeople($groupPeople);
        });
    }

    protected function getReferents(GroupPeople $groupPeople, ReferentRepository $repoReferent)
    {
        return (new FilesystemAdapter())->get(GroupPeople::CACHE_GROUP_REFERENTS_KEY.$groupPeople->getId(), function (CacheItemInterface $item) use ($groupPeople, $repoReferent) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $repoReferent->findReferentsOfGroupPeople($groupPeople);
        });
    }

    /**
     * Supprime le suivis en cache.
     *
     * @param array|supportGroup[] $supports
     */
    protected function discacheSupports($supports): void
    {
        $cache = new FilesystemAdapter();

        foreach ($supports as $supportGroup) {
            $cache->deleteItems([
                SupportGroup::CACHE_SUPPORT_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId(),
                EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(),
            ]);
        }
    }
}
