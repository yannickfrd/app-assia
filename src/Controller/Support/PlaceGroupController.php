<?php

namespace App\Controller\Support;

use App\Entity\Organization\Place;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\PlacePerson;
use App\Entity\Support\SupportGroup;
use App\Form\Organization\Place\AddPersonToPlaceGroupType;
use App\Form\Organization\Place\PlaceGroupType;
use App\Repository\Support\PlaceGroupRepository;
use App\Service\Grammar;
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
class PlaceGroupController extends AbstractController
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
     * @Route("/support/{id}/places", name="support_places", methods="GET")
     */
    public function supportPlacesGroup(int $id, SupportManager $supportManager): Response
    {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        return $this->render('app/organization/place/supportPlacesGroup.html.twig', [
            'support' => $supportGroup,
        ]);
    }

    /**
     * Nouvel hébergement.
     *
     * @Route("/support/{id}/place/new", name="support_place_new", methods="GET|POST")
     */
    public function newPlaceGroup(SupportGroup $supportGroup, PlaceGroup $placeGroup = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        // Vérifie si une prise en charge existe déjà pour le suivi
        if ($supportGroup->getPlaceGroups()) {
            foreach ($supportGroup->getPlaceGroups() as $placeGroup) {
                if (null === $placeGroup->getEndDate()) {
                    $this->addFlash('warning', 'Attention, une autre prise en charge est déjà en cours pour ce suivi.');
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

            return $this->redirectToRoute('support_places', [
                'id' => $placeGroup->getSupportGroup()->getId(),
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Une erreur s'est produite");
        }

        return $this->render('app/organization/place/placeGroup.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un hébergement.
     *
     * @Route("/support/place_group/{id}", name="support_place_edit", methods="GET|POST")
     *
     * @param int $id // PlaceGroup
     */
    public function editPlaceGroup(int $id, Request $request, SupportManager $supportManager, PlaceGroupRepository $placeGroupRepo): Response
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

        return $this->render('app/organization/place/placeGroup.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
            'addPersonForm' => $addPersonForm->createView(),
        ]);
    }

    /**
     * Ajout de personnes à la prise en charge hébergement/logement.
     *
     * @Route("/support/place_group/{id}/add_person", name="support_place_group_add_person", methods="POST")
     */
    public function addPersonToPlace(PlaceGroup $placeGroup, Request $request): Response
    {
        $supportGroup = $placeGroup->getSupportGroup();
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(AddPersonToPlaceGroupType::class, null, [
            'attr' => ['placeGroup' => $placeGroup],
        ])->handleRequest($request);

        $supportPerson = $form->get('supportPerson')->getData();

        if ($this->placeGroupManager->createPlacePerson($placeGroup, $supportPerson)) {
            $this->em->flush();

            $person = $supportPerson->getPerson();

            $this->addFlash('success', $person->getFullname().' est ajouté'.Grammar::gender($person->getGender()).' à la prise en charge.');

            $this->placeGroupManager->discacheSupport($supportGroup);
        }

        return $this->redirectToRoute('support_place_edit', ['id' => $placeGroup->getId()]);
    }

    /**
     * Supprime la prise en charge du groupe.
     *
     * @Route("/support/group-people-place/{id}/delete", name="support_group_people_place_delete", methods="GET")
     */
    public function deletePlaceGroup(PlaceGroup $placeGroup): Response
    {
        $supportGroup = $placeGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $this->em->remove($placeGroup);
        $this->em->flush();

        $this->placeGroupManager->discacheSupport($supportGroup);

        $this->addFlash('warning', 'La prise en charge est supprimée.');

        return $this->redirectToRoute('support_places', ['id' => $supportGroup->getId()]);
    }

    /**
     * Supprime la prise en charge d'une personne.
     *
     * @Route("/support/place-person/{id}/delete", name="support_person_place_delete", methods="GET")
     */
    public function deletePlacePerson(PlacePerson $placePerson): Response
    {
        $supportGroup = $placePerson->getPlaceGroup()->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $this->em->remove($placePerson);
        $this->em->flush();

        $this->placeGroupManager->discacheSupport($supportGroup);

        $this->addFlash('warning', $placePerson->getPerson()->getFullname().' est retiré de la prise en charge.');

        return $this->redirectToRoute('support_place_edit', [
            'id' => $placePerson->getPlaceGroup()->getId(),
        ]);
    }
}
