<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Form\Organization\Place\AddPersonToPlaceGroupType;
use App\Form\Organization\Place\PlaceGroupType;
use App\Repository\Support\PlaceGroupRepository;
use App\Service\Place\PlaceGroupManager;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller des hébergements des groupes de personnes.
 */
final class PlaceGroupController extends AbstractController
{
    private $em;
    private $placeGroupManager;

    public function __construct(EntityManagerInterface $em, PlaceGroupManager $placeGroupManager)
    {
        $this->em = $em;
        $this->placeGroupManager = $placeGroupManager;
    }

    /**
     * Liste des hébergements du suivi social.
     *
     * @Route("/support/{id}/places", name="support_place_group_index", methods="GET")
     */
    public function index(int $id, SupportManager $supportManager): Response
    {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        return $this->render('app/place_group/support_place_group_index.html.twig', [
            'support' => $supportGroup,
        ]);
    }

    /**
     * Nouvel hébergement.
     *
     * @Route("/support/{id}/place/new", name="support_place_new", methods="GET|POST")
     */
    public function new(SupportGroup $supportGroup, PlaceGroup $placeGroup = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // Vérifie si une prise en charge existe déjà pour le suivi
        if ($supportGroup->getPlaceGroups()) {
            foreach ($supportGroup->getPlaceGroups() as $placeGroup) {
                if (null === $placeGroup->getEndDate()) {
                    $this->addFlash('warning', 'place_group.other_exist');
                }
            }
        }

        $placeGroup = (new PlaceGroup())
            ->setSupportGroup($supportGroup)
            ->setStartDate($supportGroup->getStartDate())
            ->setEndDate($supportGroup->getEndDate());

        $form = $this->createForm(PlaceGroupType::class, $placeGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $placeGroup = $this->placeGroupManager->createPlaceGroup($supportGroup, $placeGroup);

            return $this->redirectToRoute('support_place_group_index', [
                'id' => $placeGroup->getSupportGroup()->getId(),
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'error_occurred');
        }

        return $this->render('app/place_group/place_group_edit.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/support/place_group/{id}", name="support_place_edit", methods="GET|POST")
     *
     * @param int $id // PlaceGroup
     */
    public function edit(int $id, Request $request, SupportManager $supportManager, PlaceGroupRepository $placeGroupRepo): Response
    {
        if (null === $placeGroup = $placeGroupRepo->findPlaceGroupById($id)) {
            throw $this->createAccessDeniedException();
        }

        $supportGroup = $supportManager->getSupportGroup($placeGroup->getSupportGroup()->getId());

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(PlaceGroupType::class, $placeGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->placeGroupManager->updatePlaceGroup($supportGroup, $placeGroup);

            return $this->redirectToRoute('support_place_edit', ['id' => $placeGroup->getId()]);
        }

        $addPersonForm = $this->createForm(AddPersonToPlaceGroupType::class, null, [
            'attr' => ['placeGroup' => $placeGroup],
        ]);

        return $this->render('app/place_group/place_group_edit.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
            'addPersonForm' => $addPersonForm->createView(),
        ]);
    }

    /**
     * Supprime la prise en charge du groupe.
     *
     * @Route("/support/group-people-place/{id}/delete", name="support_group_people_place_delete", methods="GET")
     */
    public function delete(PlaceGroup $placeGroup): Response
    {
        $supportGroup = $placeGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $this->em->remove($placeGroup);
        $this->em->flush();

        $this->placeGroupManager->discacheSupport($supportGroup);

        $this->addFlash('warning', 'place_group.deleted_successfully');

        return $this->redirectToRoute('support_place_group_index', ['id' => $supportGroup->getId()]);
    }
}
