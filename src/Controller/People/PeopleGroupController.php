<?php

namespace App\Controller\People;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\EntityManager\PeopleGroupManager;
use App\Form\People\PeopleGroup\PeopleGroupType;
use App\Form\People\RolePerson\RolePersonType;
use App\Repository\Organization\ReferentRepository;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\People\RolePersonRepository;
use App\Repository\Support\SupportGroupRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PeopleGroupController extends AbstractController
{
    use ErrorMessageTrait;

    private $groupManager;
    private $repo;

    public function __construct(PeopleGroupManager $groupManager, PeopleGroupRepository $repo)
    {
        $this->groupManager = $groupManager;
        $this->repo = $repo;
    }

    /**
     * Modification d'un groupe.
     *
     * @Route("/group/{id}", name="people_group_show", methods="GET|POST")
     */
    public function showPeopleGroup(int $id, Request $request, ReferentRepository $repoReferent, SupportGroupRepository $repoSuppport): Response
    {
        $peopleGroup = $this->repo->findPeopleGroupById($id);

        if (null === $peopleGroup) {
            $this->addFlash('danger', 'Ce groupe n\'existe pas.');

            return $this->redirectToRoute('home');
        }

        $form = $this->createForm(PeopleGroupType::class, $peopleGroup)
            ->handleRequest($request);

        $supports = $this->groupManager->getSupports($peopleGroup, $repoSuppport);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->groupManager->update($peopleGroup, $supports);
        }

        return $this->render('app/people/peopleGroup/peopleGroup.html.twig', [
            'form' => $form->createView(),
            'supports' => $supports,
            'referents' => $this->groupManager->getReferents($peopleGroup, $repoReferent),
        ]);
    }

    /**
     * Supprime le groupe de personnes.
     *
     * @Route("/group/{id}/delete", name="people_group_delete", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deletePeopleGroup(PeopleGroup $peopleGroup): Response
    {
        $this->groupManager->delete($peopleGroup);

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
        $peopleGroup = $this->repo->findPeopleGroupById($id);

        $rolePerson = new RolePerson();

        $form = ($this->createForm(RolePersonType::class, $rolePerson))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->groupManager->addPerson($peopleGroup, $rolePerson, $person, $repoRolePerson);
        } else {
            $this->addFlash('danger', "Une erreur s'est produite.");
        }

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Retire la personne du groupe.
     *
     * @Route("/role_person/{id}/remove/{_token}", name="role_person_remove", methods="GET")
     */
    public function tryRemovePerson(RolePerson $rolePerson, string $_token): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->accessDenied();
        }
        // Vérifie si le token est valide avant de retirer la personne du groupe
        if ($this->isCsrfTokenValid('remove'.$rolePerson->getId(), $_token)) {
            return $this->json($this->groupManager->removePerson($rolePerson));
        }

        return $this->getErrorMessage();
    }
}
