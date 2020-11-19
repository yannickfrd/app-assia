<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\GroupPeople;
use App\Entity\Person;
use App\Entity\RolePerson;
use App\Form\GroupPeople\GroupPeopleSearchType;
use App\Form\GroupPeople\GroupPeopleType;
use App\Form\Model\GroupPeopleSearch;
use App\Form\RolePerson\RolePersonType;
use App\Repository\GroupPeopleRepository;
use App\Repository\ReferentRepository;
use App\Repository\RolePersonRepository;
use App\Repository\SupportGroupRepository;
use App\Service\GroupPeopleManager;
use App\Service\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GroupPeopleController extends AbstractController
{
    use ErrorMessageTrait;

    private $groupManager;
    private $repo;

    public function __construct(GroupPeopleManager $groupManager, GroupPeopleRepository $repo)
    {
        $this->groupManager = $groupManager;
        $this->repo = $repo;
    }

    /**
     * Liste des groupes de personnes.
     *
     * @Route("/groups_people", name="groups_people", methods="GET|POST")
     */
    public function listPeopleGroups(Request $request, Pagination $pagination): Response
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

        $form = $this->createForm(GroupPeopleType::class, $groupPeople)
            ->handleRequest($request);

        $supports = $this->groupManager->getSupports($groupPeople, $repoSuppport);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->groupManager->update($groupPeople, $supports);
        }

        return $this->render('app/groupPeople/groupPeople.html.twig', [
            'form' => $form->createView(),
            'supports' => $supports,
            'referents' => $this->groupManager->getReferents($groupPeople, $repoReferent),
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
        $this->groupManager->delete($groupPeople);

        $this->addFlash('warning', 'Le groupe est supprimé.');

        return $this->redirectToRoute('home');
    }

    /**
     * Ajout d'une personne dans un groupe.
     *
     * @Route("/group/{id}/add/person/{person_id}", name="group_add_person", methods="POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function tryAddPersonInGroup(int $id, Request $request, Person $person, RolePersonRepository $repoRolePerson): Response
    {
        $groupPeople = $this->repo->findGroupPeopleById($id);

        $rolePerson = new RolePerson();

        $form = ($this->createForm(RolePersonType::class, $rolePerson))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->groupManager->addPerson($groupPeople, $rolePerson, $person, $repoRolePerson);
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
            return $this->json($this->groupManager->removePerson($rolePerson));
        }

        return $this->getErrorMessage();
    }
}
