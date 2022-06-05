<?php

namespace App\Service\SupportGroup;

use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class SupportChecker
{
    private $flashBag;

    public function __construct(RequestStack $requestStack)
    {
        /** @var Session */
        $session = $requestStack->getSession();
        $this->flashBag = $session->getFlashBag();
    }

    /**
     * Vérifie la cohérence des données du suivi social.
     */
    public function check(SupportGroup $supportGroup): void
    {
        $nbActiveSupportPeople = $this->getNbActiveSupportPeople($supportGroup);

        $this->checkNbPeople($supportGroup, $nbActiveSupportPeople);
        $this->checkStartDate($supportGroup);
        $this->checkPlaceGroup($supportGroup, $nbActiveSupportPeople);
    }

    /**
     * Vérifie la validité du demandeur principal.
     */
    public function checkValidHeader(SupportGroup $supportGroup): void
    {
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
                    $this->flashBag->add('warning', 'Le demandeur principal a été automatiquement modifié, car il ne peut pas être mineur.');
                }
            }
        }

        if (1 != $nbHeads || true === $minorHead) {
            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if ($supportPerson->getPerson()) {
                    $supportPerson->setHead(false);
                }
            }

            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if ($supportPerson->getPerson()->getAge() === $maxAge) {
                    $supportPerson->setHead(true);

                    return;
                }
            }
        }
    }

    /**
     *  Vérifie que le nombre de personnes suivies correspond à la composition familiale du groupe.
     */
    private function checkNbPeople(SupportGroup $supportGroup, int $nbActiveSupportPeople): void
    {
        $nbSupportPeople = $supportGroup->getSupportPeople()->count();
        $nbPeople = $supportGroup->getPeopleGroup()->getNbPeople();

        if ($nbSupportPeople != $nbPeople && $nbActiveSupportPeople != $nbPeople) {
            $this->flashBag->add(
                'warning',
                'Attention, le nombre de personnes suivies 
                ne correspond pas à la composition familiale du groupe ('.$nbPeople.' personnes).'
            );
        }
    }

    private function checkStartDate(SupportGroup $supportGroup): void
    {
        if (SupportGroup::STATUS_IN_PROGRESS === $supportGroup->getStatus() && null === $supportGroup->getStartDate()) {
            $this->flashBag->add('warning', "Attention, la date de début d'accompagnement n'est pas renseignée.");
        }
    }

    /**
     * @return mixed
     */
    private function checkPlaceGroup(SupportGroup $supportGroup, int $nbActiveSupportPeople)
    {
        if ($supportGroup->getDevice() && Choices::YES === $supportGroup->getDevice()->getPlace()) {
            // Vérifie qu'il y a un hébergement créé
            if (0 === $supportGroup->getPlaceGroups()->count()) {
                return $this->flashBag->add('warning', 'Attention, aucun hébergement n\'est enregistré pour ce suivi.');
            }
            // Vérifie que le nombre de personnes suivies correspond au nombre de personnes hébergées
            $nbPlacePeople = $this->getNbPlacePeople($supportGroup);
            if (!$supportGroup->getEndDate() && $nbActiveSupportPeople != $nbPlacePeople) {
                $this->flashBag->add(
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
