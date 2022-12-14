<?php

declare(strict_types=1);

namespace App\Controller\People;

use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Form\Admin\Security\SiSiaoLoginType;
use App\Form\Model\SiSiao\SiSiaoLogin;
use App\Form\People\PeopleGroup\PeopleGroupType;
use App\Form\People\RolePerson\RolePersonType;
use App\Repository\People\PeopleGroupRepository;
use App\Service\People\PeopleGroupCollections;
use App\Service\People\PeopleGroupManager;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PeopleGroupController extends AbstractController
{
    private $peopleGroupRepo;
    private $em;

    public function __construct(EntityManagerInterface $em, PeopleGroupRepository $peopleGroupRepo)
    {
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->em = $em;
    }

    /**
     * Voir ou modifier un groupe.
     *
     * @Route("/group/{id}", name="people_group_show", methods="GET|POST")
     */
    public function showPeopleGroup(int $id, Request $request, PeopleGroupCollections $peopleGroupCollections,
        PeopleGroupManager $peopleGroupManager): Response
    {
        if (null === $peopleGroup = $this->peopleGroupRepo->findPeopleGroupById($id)) {
            throw $this->createAccessDeniedException('Ce groupe n\'existe pas.');
        }

        $form = $this->createForm(PeopleGroupType::class, $peopleGroup)
            ->handleRequest($request);

        $supports = $peopleGroupCollections->getSupports($peopleGroup);

        $siSiaoLoginForm = $this->createForm(SiSiaoLoginType::class, new SiSiaoLogin());

        if ($form->isSubmitted() && $form->isValid()) {
            $peopleGroupManager->update($peopleGroup);

            $this->addFlash('success', 'people_group.updated_successfully');
        }

        return $this->render('app/people/peopleGroup/people_group_edit.html.twig', [
            'form' => $form->createView(),
            'supports' => $supports,
            'referents' => $peopleGroupCollections->getReferents($peopleGroup),
            'siSiaoLoginForm' => $siSiaoLoginForm->createView(),
        ]);
    }

    /**
     * Supprime un groupe de personnes.
     *
     * @Route("/group/{id}/delete", name="people_group_delete", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function deletePeopleGroup(PeopleGroup $peopleGroup): Response
    {
        PeopleGroupManager::deleteCacheItems($peopleGroup);

        $this->em->remove($peopleGroup);
        $this->em->flush();

        $this->addFlash('warning', 'people_group.deleted_successfully');

        return $this->redirectToRoute('home');
    }

    /**
     * Ajoute une personne dans un groupe.
     *
     * @Route("/group/{id}/add_person/{person_id}", name="group_add_person", methods="POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function addPersonToGroup(int $id, Request $request, Person $person, PeopleGroupManager $peopleGroupManager,
        SupportManager $supportManager): Response
    {
        if (null === $peopleGroup = $this->peopleGroupRepo->findPeopleGroupById($id)) {
            throw $this->createAccessDeniedException('Ce groupe n\'existe pas.');
        }

        $form = $this->createForm(RolePersonType::class, $rolePerson = new RolePerson())
            ->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $peopleGroupManager->addPerson($peopleGroup, $person, $rolePerson, $form->get('addPersonToSupport')->getData());
        } else {
            $this->addFlash('danger', 'error_occurred');
        }

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Retire une personne du groupe.
     *
     * @Route("/role_person/{id}/remove/{_token}", name="role_person_remove", methods="GET")
     */
    public function removePerson(RolePerson $rolePerson, string $_token, PeopleGroupManager $peopleGroupManager): Response
    {
        $peopleGroup = $rolePerson->getPeopleGroup();

        if ($this->isCsrfTokenValid('remove'.$rolePerson->getId(), $_token)) {
            $peopleGroupManager->removePerson($rolePerson);
        } else {
            $this->addFlash('danger', 'error_occurred');
        }

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }
}
