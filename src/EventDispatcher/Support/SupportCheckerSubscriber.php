<?php

namespace App\EventDispatcher\Support;

use App\Form\Utils\Choices;
use App\Entity\Support\SupportGroup;
use App\Event\Support\SupportGroupEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SupportCheckerSubscriber implements EventSubscriberInterface
{
    private $flashbag;

    public function __construct(FlashBagInterface $flashbag)
    {
        $this->flashbag = $flashbag;
    }

    public static function getSubscribedEvents()
    {
        return [
            'support.view' => 'checkSupportGroup',
        ];
    }

    /**
     * Vérifie la cohérence des données du suivi social.
     */
    public function checkSupportGroup(SupportGroupEvent $event): void
    {
        $supportGroup = $event->getSupportGroup();
        $nbActiveSupportPeople = $this->getNbActiveSupportPeople($supportGroup);

        $this->checkNbPeople($supportGroup, $nbActiveSupportPeople);
        $this->checkStartDate($supportGroup);
        $this->checkPlaceGroup($supportGroup, $nbActiveSupportPeople);
    }

    /**
     *  Vérifie que le nombre de personnes suivies correspond à la composition familiale du groupe.
     */
    private function checkNbPeople(SupportGroup $supportGroup, int $nbActiveSupportPeople)
    {
        $nbSupportPeople = $supportGroup->getSupportPeople()->count();
        $nbPeople = $supportGroup->getPeopleGroup()->getNbPeople();

        if ($nbSupportPeople != $nbPeople && $nbActiveSupportPeople != $nbPeople) {
            $this->flashbag->add(
                'warning',
                'Attention, le nombre de personnes suivies 
                ne correspond pas à la composition familiale du groupe ('.$nbPeople.' personnes).'
            );
        }
    }

    private function checkStartDate(SupportGroup $supportGroup)
    {
        if (SupportGroup::STATUS_IN_PROGRESS === $supportGroup->getStatus() && null === $supportGroup->getStartDate()) {
            $this->flashbag->add('warning', "Attention, la date de début d'accompagnement n'est pas renseignée.");
        }
    }

    private function checkPlaceGroup(SupportGroup $supportGroup, int $nbActiveSupportPeople)
    {
        if ($supportGroup->getDevice() && Choices::YES === $supportGroup->getDevice()->getPlace()) {
            // Vérifie qu'il y a un hébergement créé
            if (0 === $supportGroup->getPlaceGroups()->count()) {
                return $this->flashbag->add('warning', 'Attention, aucun hébergement n\'est enregistré pour ce suivi.');
            }
            // Vérifie que le nombre de personnes suivies correspond au nombre de personnes hébergées
            $nbPlacePeople = $this->getNbPlacePeople($supportGroup);
            if (!$supportGroup->getEndDate() && $nbActiveSupportPeople != $nbPlacePeople) {
                $this->flashbag->add(
                    'warning',
                    'Attention, le nombre de personnes suivies ('.$nbActiveSupportPeople.') 
                    ne correspond pas au nombre de personnes hébergées ('.$nbPlacePeople.').<br/> 
                    Allez dans l\'onglet <b>Hébergement</b> pour ajouter les personnes à l\'hébergement.'
                );
            }
        }
    }

    private function getNbActiveSupportPeople(SupportGroup $supportGroup): int
    {
        $count = 0;

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (null === $supportPerson->getEndDate()) {
                ++$count;
            }
        }

        return $count;
    }

    private function getNbPlacePeople(SupportGroup $supportGroup): int
    {
        $count = 0;

        foreach ($supportGroup->getPlaceGroups() as $placeGroup) {
            if (null === $placeGroup->getEndDate()) {
                foreach ($placeGroup->getPlacePeople() as $placePerson) {
                    if (null === $placePerson->getEndDate()) {
                        ++$count;
                    }
                }
            }
        }

        return $count;
    }
}
