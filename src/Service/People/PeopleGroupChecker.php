<?php

namespace App\Service\People;

use App\Entity\People\PeopleGroup;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PeopleGroupChecker
{
    private $flashBag;

    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    /**
     * Check is the header is valid.
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
                    $this->flashBag->add('warning', 'people_group.header_automatically_modified');
                }
            }
        }

        // If more of 1 header or header is a child, then set the older person as header
        if (1 !== $nbHeads || true === $minorHead) {
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
