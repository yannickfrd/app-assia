<?php

namespace App\Service\Place;

use App\Entity\Organization\Place;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\PlacePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlaceGroupManager
{
    private $em;
    private $flashBag;
    private $translator;

    public function __construct(
        EntityManagerInterface $em,
        RequestStack $requestStack,
        TranslatorInterface $translator
    ) {
        $this->em = $em;
        /** @var Session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
        $this->translator = $translator;
    }

    /**
     * Crée la prise en charge du groupe.
     */
    public function createPlaceGroup(SupportGroup $supportGroup, ?PlaceGroup $placeGroup = null, ?Place $place = null): PlaceGroup
    {
        if (null === $placeGroup) {
            $placeGroup = (new PlaceGroup())
                ->setPlace($place)
                ->setStartDate($supportGroup->getStartDate())
                ->setEndDate($supportGroup->getEndDate())
            ;

            $supportGroup->addPlaceGroup($placeGroup);
        }

        $placeGroup->setPeopleGroup($supportGroup->getPeopleGroup());

        $this->em->persist($placeGroup);

        $this->createPlacePeople($supportGroup, $placeGroup);

        $this->updateLocationSupportGroup($supportGroup, $placeGroup->getPlace());

        $this->em->flush();

        $this->discacheSupport($supportGroup);

        $this->flashBag->add('success', 'place_group.created_successfully');

        return $placeGroup;
    }

    /**
     * Met à jour l'adresse du suivi via l'adresse du groupe de places.
     */
    protected function updateLocationSupportGroup(SupportGroup $supportGroup, Place $place): void
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
    public function updatePlaceGroup(SupportGroup $supportGroup, PlaceGroup $placeGroup): PlaceGroup
    {
        foreach ($placeGroup->getPlacePeople() as $placePerson) {
            $person = $placePerson->getPerson();

            $placePerson->setStartDate($placeGroup->getStartDate());

            if ($placePerson->getStartDate() < $person->getBirthdate()) {
                $placePerson->setStartDate($person->getBirthdate());

                $this->flashBag->add('warning', $this->translator->trans('place_person.invalid_start_date', [
                    'person_fullname' => $person->getFullname(),
                ], 'app'));
            }
            if (null === $placePerson->getEndDate()) {
                $placePerson->setEndDate($placeGroup->getEndDate());
            }

            if (null === $placePerson->getEndReason()) {
                $placePerson->setEndReason($placeGroup->getEndReason());
            }
        }
        $this->em->flush();

        $this->flashBag->add('success', 'place_group.updated_successfully');

        $this->discacheSupport($supportGroup);

        return $placeGroup;
    }

    /**
     * Crée les prises en charge individuelles.
     */
    protected function createPlacePeople(SupportGroup $supportGroup, PlaceGroup $placeGroup): int
    {
        $count = 0;

        foreach ($placeGroup->getSupportGroup()->getSupportPeople() as $supportPerson) {
            // Vérifie si la personne n'est pas déjà rattachée à la prise en charge
            if ((null === $supportPerson->getEndDate() || 0 === $supportGroup->getPlaceGroups()->count())
                && !in_array($supportPerson->getPerson()->getId(), $this->getPeopleInPlace($placeGroup))) {
                $this->createPlacePerson($placeGroup, $supportPerson);
                ++$count;
            }
        }
        $this->em->flush();

        return $count;
    }

    public function createPlacePerson(PlaceGroup $placeGroup, SupportPerson $supportPerson): PlacePerson
    {
        $person = $supportPerson->getPerson();

        $placePerson = (new PlacePerson())
            ->setStartDate($placeGroup->getStartDate())
            ->setEndDate($placeGroup->getEndDate())
            ->setEndReason($placeGroup->getEndReason())
            ->setCommentEndReason($placeGroup->getCommentEndReason())
            ->setPerson($person)
        ;

        $placeGroup->addPlacePerson($placePerson);
        $supportPerson->addPlacesPerson($placePerson);

        // Vérifie si la date de prise en charge n'est pas antérieure à la date de naissance
        if ($placePerson->getStartDate() < $person->getBirthdate()) {
            // Si c'est le cas, on prend en compte la date de naissance
            $placePerson->setStartDate($person->getBirthdate());

            $this->flashBag->add('warning', $this->translator->trans('place_person.invalid_start_date', [
                'person_fullname' => $person->getFullname(),
            ], 'app'));
        }

        $this->em->persist($placePerson);

        return $placePerson;
    }

    // Donne les ID des personnes rattachées à la prise en charge.
    protected function getPeopleInPlace(PlaceGroup $placeGroup): array
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
        return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))
            ->deleteItem(SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId());
    }
}
