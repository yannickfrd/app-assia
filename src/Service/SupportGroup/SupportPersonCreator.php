<?php

namespace App\Service\SupportGroup;

use App\Entity\People\RolePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;

trait SupportPersonCreator
{
    /**
     * Crée un suivi individuel.
     */
    public function createSupportPerson(SupportGroup $supportGroup, RolePerson $rolePerson): SupportPerson
    {
        $supportPerson = (new SupportPerson())
            ->setSupportGroup($supportGroup)
            ->setPerson($rolePerson->getPerson())
            ->setHead($rolePerson->getHead())
            ->setRole($rolePerson->getRole())
            ->setStatus($supportGroup->getStatus())
            ->setStartDate($supportGroup->getStartDate())
            ->setEndDate($supportGroup->getEndDate())
            ->setEndStatus($supportGroup->getEndStatus())
            ->setEndStatusComment($supportGroup->getEndStatusComment());

        $birthdate = $rolePerson->getPerson()->getBirthdate();

        if ($supportPerson->getStartDate() && $supportPerson->getStartDate() < $birthdate) {
            $supportPerson->setStartDate($birthdate);
            // $this->addFlash('warning', $supportPerson->getPerson()->getFullname().' : la date de début de suivi retenue est sa date de naissance.');
        }

        return $supportPerson;
    }
}
