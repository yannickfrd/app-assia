<?php

namespace App\Service\People;

use App\Entity\People\PeopleGroup;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PeopleGroupChecker
{
    private $flashbag;

    public function __construct(FlashBagInterface $flashbag)
    {
        $this->flashbag = $flashbag;
    }

    /**
     * Vérifie la validité du demandeur principal.
     */
    public function checkValidHeader(PeopleGroup $peopleGroup): void
    {
        $nbHeads = 0;
        $maxAge = 0;
        $minorHead = false;

        foreach ($peopleGroup->getRolePeople() as $rolePerson) {
            $age = $rolePerson->getPerson()->getAge();
            if ($age > $maxAge) {
                $maxAge = $age;
            }
            if (true === $rolePerson->getHead()) {
                ++$nbHeads;
                if ($age < 18) {
                    $minorHead = true;
                    $this->flashbag->add('warning', 'Le demandeur principal a été automatiquement modifié, car il ne peut pas être mineur.');
                }
            }
        }

        if (1 != $nbHeads || true === $minorHead) {
            foreach ($peopleGroup->getRolePeople() as $rolePerson) {
                $rolePerson->setHead(false);
            }
            foreach ($peopleGroup->getRolePeople() as $rolePerson) {
                if ($rolePerson->getPerson()->getAge() === $maxAge) {
                    $rolePerson->setHead(true);

                    return;
                }
            }
        }
    }
}