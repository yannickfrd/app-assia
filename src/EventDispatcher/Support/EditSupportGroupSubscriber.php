<?php

namespace App\EventDispatcher\Support;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Event\Support\SupportGroupEvent;
use App\Service\SupportGroup\AvdlService;
use App\Service\SupportGroup\HotelSupportService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class EditSupportGroupSubscriber implements EventSubscriberInterface
{
    private $flashbag;
    private $cache;

    public function __construct(FlashBagInterface $flashbag)
    {
        $this->flashbag = $flashbag;
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    public static function getSubscribedEvents()
    {
        return [
            'support_group.before_create' => [
                ['checkValidHead', 10],
                ['updateNbPeople', 0],
            ],
            'support_group.after_create' => [
                ['discache', -50],
            ],
            'support_group.before_update' => [
                ['update', 50],
                ['checkValidHead', 10],
                ['updateNbPeople', 0],
            ],
            'support_group.after_update' => [
                ['discache', -50],
            ],
        ];
    }

    public function update(SupportGroupEvent $event)
    {
        $supportGroup = $event->getSupportGroup();

        $supportGroup->setUpdatedAt(new \DateTime());
        $serviceType = $supportGroup->getService()->getType();

        // Vérifie le service du suivi
        if (Service::SERVICE_TYPE_AVDL === $serviceType) {
            $supportGroup = (new AvdlService())->updateSupportGroup($supportGroup);
        }
        if (Service::SERVICE_TYPE_HOTEL === $serviceType) {
            $supportGroup = (new HotelSupportService())->updateSupportGroup($supportGroup);
        }

        $this->updateSupportPeople($supportGroup);
        $this->updatePlaceGroup($supportGroup);
    }

    /**
     * Met à jour les suivis sociales individuelles des personnes.
     */
    protected function updateSupportPeople(SupportGroup $supportGroup): void
    {
        $nbPeople = $supportGroup->getSupportPeople()->count();

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            // Si c'est une personne seule ou si la date de début de suivi est vide, copie la date de début de suivi.
            if (1 === $nbPeople || null === $supportPerson->getStartDate()) {
                $supportPerson->setStartDate($supportGroup->getStartDate());
            }
            if (1 === $nbPeople || null === $supportPerson->getEndDate() || null === $supportPerson->getEndStatus()) {
                $supportPerson
                    ->setStatus($supportGroup->getStatus())
                    ->setEndDate($supportGroup->getEndDate())
                    ->setEndStatus($supportGroup->getEndStatus())
                    ->setEndStatusComment($supportGroup->getEndStatusComment());
            }
            if ($supportPerson->getEndDate()) {
                $supportPerson->setStatus(SupportGroup::STATUS_ENDED);
            }
            if (null === $supportPerson->getStatus() && $supportPerson->getEndDate()) {
                $supportPerson->setStatus($supportGroup->getStatus());
            }
            if (null === $supportPerson->getEndStatus() && $supportPerson->getEndDate()) {
                $supportPerson->setEndStatus($supportGroup->getEndStatus());
            }
            if (null === $supportPerson->getEndStatusComment() && $supportPerson->getEndDate()) {
                $supportPerson->setEndStatusComment($supportGroup->getEndStatusComment());
            }

            // Vérifie si la date de suivi n'est pas antérieure à la date de naissance.
            $person = $supportPerson->getPerson();
            if ($supportPerson->getStartDate() && $person && $supportPerson->getStartDate() < $person->getBirthdate()) {
                // Si c'est le cas, on prend en compte la date de naissance
                $supportPerson->setStartDate($person->getBirthdate());
                // $this->addFlash('light', $supportPerson->getPerson()->getFullname().' : la date de début de suivi retenue est sa date de naissance.');
            }
        }
    }

    /**
     * Met à jour le nombre de personnes du suivi.
     */
    public function updateNbPeople(SupportGroupEvent $event): void
    {
        $supportGroup = $event->getSupportGroup();

        $today = new \DateTime();
        $nbPeople = 0;
        $nbChildrenUnder3years = 0;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if ($supportPerson->getEndDate() === $supportGroup->getEndDate()) {
                $birthdate = $supportPerson->getPerson()->getBirthdate();
                $age = $birthdate->diff($supportPerson->getEndDate() ?? $today)->y ?? 0;
                if ($age < 3) {
                    ++$nbChildrenUnder3years;
                }
                ++$nbPeople;
            }
        }
        $supportGroup->setNbPeople($nbPeople);
        $supportGroup->setNbChildrenUnder3years($nbChildrenUnder3years);
    }

    /**
     * Met à jour la prise en charge du groupe.
     */
    protected function updatePlaceGroup(SupportGroup $supportGroup): void
    {
        // Si le statut du suivi est égal à terminé et si  "Fin d'hébergement" coché, alors met à jour la prise en charge
        if (SupportGroup::STATUS_ENDED === $supportGroup->getStatus() && $supportGroup->getEndPlace()) {
            foreach ($supportGroup->getPlaceGroups() as $placeGroup) {
                if (!$placeGroup->getEndDate()) {
                    null === $placeGroup->getEndDate() ? $placeGroup->setEndDate($supportGroup->getEndDate()) : null;
                    null === $placeGroup->getEndReason() ? $placeGroup->setEndReason(PlaceGroup::END_REASON_SUPPORT_ENDED) : null;

                    $this->updatePlacePeople($placeGroup);
                }
            }
        }
    }

    /**
     * Met à jour la prise en charge des personnes du groupe.
     */
    protected function updatePlacePeople(PlaceGroup $placeGroup): void
    {
        foreach ($placeGroup->getPlacePeople() as $placePerson) {
            $supportPerson = $placePerson->getSupportPerson();
            $person = $supportPerson->getPerson();

            null === $placePerson->getEndDate() ? $placePerson->setEndDate($supportPerson->getEndDate()) : null;
            null === $placePerson->getEndReason() ? $placePerson->setEndReason(PlaceGroup::END_REASON_SUPPORT_ENDED) : null;

            if ($supportPerson->getStartDate() && $supportPerson->getStartDate() < $person->getBirthdate()) {
                $supportPerson->setStartDate($person->getBirthdate());
                $this->flashbag->add('warning', 'La date de début d\'hébergement ne peut pas être antérieure à la date de naissance de la personne ('.$placePerson->getPerson()->getFullname().').');
            }
        }
    }

    /**
     * Vérifie la validité du demandeur principal.
     */
    public function checkValidHead(SupportGroupEvent $event): void
    {
        $supportGroup = $event->getSupportGroup();

        $nbHeads = 0;
        $maxAge = 0;
        $minorHead = false;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (null === $supportPerson->getPerson()) {
                continue;
            }

            $age = $supportPerson->getPerson()->getAge();

            if ($age > $maxAge) {
                $maxAge = $age;
            }

            if (true === $supportPerson->getHead()) {
                ++$nbHeads;
                if ($age < 18) {
                    $minorHead = true;
                    $this->flashbag->add('warning', 'Le demandeur principal a été automatiquement modifié, car il ne peut pas être mineur.');
                }
            }
        }

        if (1 != $nbHeads || true === $minorHead) {
            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if (null === $supportPerson->getPerson()) {
                    continue;
                }

                $supportPerson->setHead(false);

                if ($supportPerson->getPerson()->getAge() === $maxAge) {
                    $supportPerson->setHead(true);
                }
            }
        }
    }

    /**
     * Vide le cache du suivi social et des indicateurs du service.
     */
    public function discache(SupportGroupEvent $event): bool
    {
        $supportGroup = $event->getSupportGroup();
        $id = $supportGroup->getId();

        if ($supportGroup->getReferent()) {
            $this->cache->deleteItem(User::CACHE_USER_SUPPORTS_KEY.$supportGroup->getReferent()->getId());
        }

        return $this->cache->deleteItems([
            PeopleGroup::CACHE_GROUP_SUPPORTS_KEY.$supportGroup->getPeopleGroup()->getId(),
            SupportGroup::CACHE_SUPPORT_KEY.$id,
            SupportGroup::CACHE_FULLSUPPORT_KEY.$id,
            EvaluationGroup::CACHE_EVALUATION_KEY.$id,
            Service::CACHE_INDICATORS_KEY.$supportGroup->getService()->getId(),
        ]);
    }
}
