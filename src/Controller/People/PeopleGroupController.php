<?php

namespace App\Controller\People;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Event\People\PeopleGroupEvent;
use App\Form\People\PeopleGroup\PeopleGroupType;
use App\Form\People\RolePerson\RolePersonType;
use App\Repository\People\PeopleGroupRepository;
use App\Service\People\PeopleGroupCollections;
use App\Service\People\PeopleGroupManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PeopleGroupController extends AbstractController
{
    use ErrorMessageTrait;

    private $peopleGroupRepo;
    private $manager;

    public function __construct(EntityManagerInterface $manager, PeopleGroupRepository $peopleGroupRepo)
    {
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->manager = $manager;
    }

    /**
     * Voir ou modifier un groupe.
     *
     * @Route("/group/{id}", name="people_group_show", methods="GET|POST")
     */
    public function showPeopleGroup(int $id, Request $request, PeopleGroupCollections $peopleGroupCollections, EventDispatcherInterface $dispatcher): Response
    {
        $peopleGroup = $this->peopleGroupRepo->findPeopleGroupById($id);

        if (null === $peopleGroup) {
            throw new AccessDeniedException('Ce groupe n\'existe pas.');
        }

        $form = $this->createForm(PeopleGroupType::class, $peopleGroup)
            ->handleRequest($request);

        $supports = $peopleGroupCollections->getSupports($peopleGroup);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new PeopleGroupEvent($peopleGroup), 'people_group.before_update');

            $this->manager->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');

            $dispatcher->dispatch(new PeopleGroupEvent($peopleGroup, $supports), 'people_group.after_update');
        }

        return $this->render('app/people/peopleGroup/peopleGroup.html.twig', [
            'form' => $form->createView(),
            'supports' => $supports,
            'referents' => $peopleGroupCollections->getReferents($peopleGroup),
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
        $this->manager->remove($peopleGroup);
        $this->manager->flush();

        $this->addFlash('warning', 'Le groupe est supprimé.');

        return $this->redirectToRoute('home');
    }

    /**
     * Ajoute une personne dans un groupe.
     *
     * @Route("/group/{id}/add_person/{person_id}", name="group_add_person", methods="POST")
     * @ParamConverter("person", options={"id" = "person_id"})
     */
    public function addPersonInGroup(int $id, Request $request, Person $person, PeopleGroupManager $peopleGroupManager): Response
    {
        $peopleGroup = $this->peopleGroupRepo->findPeopleGroupById($id);

        $form = $this->createForm(RolePersonType::class, $rolePerson = new RolePerson())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $peopleGroupManager->addPerson($peopleGroup, $person, $rolePerson);
        } else {
            $this->addFlash('danger', 'Une erreur s\'est produite.');
        }

        return $this->redirectToRoute('people_group_show', ['id' => $peopleGroup->getId()]);
    }

    /**
     * Retire une personne du groupe.
     *
     * @Route("/role_person/{id}/remove/{_token}", name="role_person_remove", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function removePerson(RolePerson $rolePerson, string $_token, PeopleGroupManager $peopleGroupManager): Response
    {
        if ($this->isCsrfTokenValid('remove'.$rolePerson->getId(), $_token)) {
            $data = $peopleGroupManager->removePerson($rolePerson);

            return $this->json($data);
        }

        return $this->getErrorMessage();
    }
}
