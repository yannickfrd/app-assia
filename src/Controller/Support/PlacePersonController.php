<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Entity\Support\PlaceGroup;
use App\Entity\Support\PlacePerson;
use App\Form\Organization\Place\AddPersonToPlaceGroupType;
use App\Service\Grammar;
use App\Service\Place\PlaceGroupManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PlacePersonController extends AbstractController
{
    private $em;
    private $placeGroupManager;
    private $translator;

    public function __construct(
        EntityManagerInterface $em,
        PlaceGroupManager $placeGroupManager,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        $this->placeGroupManager = $placeGroupManager;
        $this->translator = $translator;
    }

    /**
     * Ajout de personnes à la prise en charge hébergement/logement.
     *
     * @Route("/support/place_group/{id}/add_person", name="support_place_group_add_person", methods="POST")
     */
    public function add(PlaceGroup $placeGroup, Request $request): Response
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

            $this->addFlash('success', $this->translator->trans('place_person.add_successfully', [
                'person_fullname' => $person->getFullname(),
                'e' => Grammar::gender($person->getGender()),
            ], 'app'));

            $this->placeGroupManager->discacheSupport($supportGroup);
        }

        return $this->redirectToRoute('support_place_edit', ['id' => $placeGroup->getId()]);
    }

    /**
     * Supprime la prise en charge d'une personne.
     *
     * @Route("/support/place-person/{id}/delete", name="support_person_place_delete", methods="GET")
     */
    public function delete(PlacePerson $placePerson): Response
    {
        $supportGroup = $placePerson->getPlaceGroup()->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $this->em->remove($placePerson);
        $this->em->flush();

        $this->placeGroupManager->discacheSupport($supportGroup);

        $this->addFlash('warning', $this->translator->trans('place_person.removed_successfully', [
            'person_fullname' => $placePerson->getPerson()->getFullname(),
            'e' => Grammar::gender($placePerson->getPerson()->getGender()),
        ], 'app'));

        return $this->redirectToRoute('support_place_edit', [
            'id' => $placePerson->getPlaceGroup()->getId(),
        ]);
    }
}
