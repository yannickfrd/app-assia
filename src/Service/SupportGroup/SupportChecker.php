<?php

namespace App\Service\SupportGroup;

use App\Entity\Support\SupportGroup;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class SupportChecker
{
    private $flashbag;

    public function __construct(FlashBagInterface $flashbag)
    {
        $this->flashbag = $flashbag;
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
                    $this->flashbag->add('warning', 'Le demandeur principal a été automatiquement modifié, car il ne peut pas être mineur.');
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
}
