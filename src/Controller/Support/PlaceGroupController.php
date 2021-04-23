<?php

namespace App\Controller\Support;

use App\Entity\Organization\Place;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\PlacePerson;
use App\Entity\Support\SupportGroup;
use App\Form\Organization\Place\PlaceGroupType;
use App\Repository\Support\PlaceGroupRepository;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller des hébergements des groupes de personnes.
 */
class PlaceGroupController extends AbstractController
{
    private $manager;
    private $placeGroupRepo;

    public function __construct(EntityManagerInterface $manager, PlaceGroupRepository $placeGroupRepo)
    {
        $this->manager = $manager;
        $this->placeGroupRepo = $placeGroupRepo;
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
            return $this->createPlaceGroup($supportGroup, $placeGroup);
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
    public function editPlaceGroup(int $id, SupportManager $supportManager, Request $request): Response
    {
        $placeGroup = $this->placeGroupRepo->findPlaceGroupById($id);
        $supportGroup = $supportManager->getSupportGroup($placeGroup->getSupportGroup()->getId());

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(PlaceGroupType::class, $placeGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updatePlaceGroup($supportGroup, $placeGroup);
        }

        return $this->render('app/organization/place/placeGroup.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Ajout de personnes à la prise en charge.
     *
     * @Route("/support/group_people_place/{id}/add_people", name="support_group_people_place_add_people", methods="GET")
     */
    public function addPeopleInPlace(PlaceGroup $placeGroup): Response
    {
        $supportGroup = $placeGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $countAddPeople = $this->createPlacePeople($supportGroup, $placeGroup);

        if ($countAddPeople >= 1) {
            $this->addFlash('success', $countAddPeople.' personne(s) sont ajoutée(s) à la prise en charge.');
            $this->discacheSupport($supportGroup);
        } else {
            $this->addFlash('warning', 'Aucune personne n\'a été ajoutée.');
        }

        return $this->redirectToRoute('support_place_edit', [
            'id' => $placeGroup->getId(),
        ]);
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

        $this->manager->remove($placeGroup);
        $this->manager->flush();

        $this->discacheSupport($supportGroup);

        $this->addFlash('warning', 'La prise en charge est supprimée.');

        return $this->redirectToRoute('support_places', ['id' => $supportGroup->getId()]);
    }

    /**
     * Supprime la prise en charge d'une personne.
     *
     * @Route("/support/person-place/{id}/delete", name="support_person_place_delete", methods="GET")
     */
    public function deletePlacePerson(PlacePerson $placePerson): Response
    {
        $supportGroup = $placePerson->getPlaceGroup()->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $this->manager->remove($placePerson);
        $this->manager->flush();

        $this->discacheSupport($supportGroup);

        $this->addFlash('warning', $placePerson->getPerson()->getFullname().' est retiré de la prise en charge.');

        return $this->redirectToRoute('support_place_edit', [
            'id' => $placePerson->getPlaceGroup()->getId(),
        ]);
    }

    /**
     * Crée la prise en charge du groupe.
     */
    protected function createPlaceGroup(SupportGroup $supportGroup, PlaceGroup $placeGroup): Response
    {
        $placeGroup->setPeopleGroup($supportGroup->getPeopleGroup());

        $this->manager->persist($placeGroup);

        $this->createPlacePeople($supportGroup, $placeGroup);

        $this->updateLocationSupportGroup($supportGroup, $placeGroup->getPlace());

        $this->manager->flush();

        $this->discacheSupport($supportGroup);

        $this->addFlash('success', "L'hébergement est créé.");

        return $this->redirectToRoute('support_places', [
            'id' => $placeGroup->getSupportGroup()->getId(),
        ]);
    }

    /**
     * Met à jour l'adresse du suivi via l'adresse du groupe de places.
     */
    protected function updateLocationSupportGroup(SupportGroup $supportGroup, Place $place)
    {
        $supportGroup
            ->setAddress($place->getAddress())
            ->setCity($place->getCity())
            ->setZipcode($place->getZipcode())
            ->setCommentLocation($place->getCommentLocation())
            ->setLocationId($place->getLocationId())
            ->setLat($place->getLat())
            ->setLon($place->getLon());
    }

    /**
     * Met à jour la prise en charge du groupe.
     */
    protected function updatePlaceGroup(SupportGroup $supportGroup, PlaceGroup $placeGroup)
    {
        foreach ($placeGroup->getPlacePeople() as $placePerson) {
            $person = $placePerson->getPerson();
            // if (null === $placePerson->getEndDate()) {
            $placePerson->setStartDate($placeGroup->getStartDate());
            // }
            if ($placePerson->getStartDate() < $person->getBirthdate()) {
                $placePerson->setStartDate($person->getBirthdate());
                $this->addFlash('warning', 'La date de début d\'hébergement ne peut pas être antérieure à la date de naissance de la personne ('.$person->getFullname().').');
            }
            if (null === $placePerson->getEndDate()) {
                $placePerson->setEndDate($placeGroup->getEndDate());
            }

            if (null === $placePerson->getEndReason()) {
                $placePerson->setEndReason($placeGroup->getEndReason());
            }
        }
        $this->manager->flush();

        $this->addFlash('success', 'L\'hébergement est mis à jour');

        $this->discacheSupport($supportGroup);

        return $this->redirectToRoute('support_place_edit', ['id' => $placeGroup->getId()]);
    }

    /**
     * Crée les prises en charge individuelles.
     */
    protected function createPlacePeople(SupportGroup $supportGroup, PlaceGroup $placeGroup): int
    {
        $countAddPeople = 0;
        foreach ($placeGroup->getSupportGroup()->getSupportPeople() as $supportPerson) {
            // Vérifie si la personne n'est pas déjà rattachée à la prise en charge
            if ((null === $supportPerson->getEndDate() || 0 === $supportGroup->getPlaceGroups()->count())
                && !in_array($supportPerson->getPerson()->getId(), $this->getPeopleInPlace($placeGroup))) {
                // Si elle n'est pas déjà pris en charge, on la créé
                $placePerson = (new PlacePerson())
                    ->setStartDate($placeGroup->getStartDate())
                    ->setEndDate($placeGroup->getEndDate())
                    ->setEndReason($placeGroup->getEndReason())
                    ->setCommentEndReason($placeGroup->getCommentEndReason())
                    ->setPlaceGroup($placeGroup)
                    ->setSupportPerson($supportPerson)
                    ->setPerson($supportPerson->getPerson());

                // Vérifie si la date de prise enn charge n'est pas antérieure à la date de naissance
                $person = $supportPerson->getPerson();
                if ($placePerson->getStartDate() < $person->getBirthdate()) {
                    // Si c'est le cas, on prend en compte la date de naissance
                    $placePerson->setStartDate($person->getBirthdate());
                }

                $this->manager->persist($placePerson);
                ++$countAddPeople;
            }
        }
        $this->manager->flush();

        return $countAddPeople;
    }

    // Donne les ID des personnes rattachées à la prise en charge.
    protected function getPeopleInPlace(PlaceGroup $placeGroup)
    {
        $people = [];
        foreach ($placeGroup->getPlacePeople() as $placePerson) {
            $people[] = $placePerson->getPerson()->getId();
        }

        return $people;
    }

    /**
     * Supprime l'item en cache du suivi social.
     */
    public function discacheSupport(SupportGroup $supportGroup): bool
    {
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->deleteItem(SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId());
    }
}
