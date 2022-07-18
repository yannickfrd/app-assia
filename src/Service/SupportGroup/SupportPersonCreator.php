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
            ->setEndReason($supportGroup->getEndReason())
            ->setEndStatus($supportGroup->getEndStatus())
            ->setEndStatusComment($supportGroup->getEndStatusComment());

        $birthdate = $rolePerson->getPerson()->getBirthdate();

        if ($supportPerson->getStartDate() && $supportPerson->getStartDate() < $birthdate) {
            $supportPerson->setStartDate($birthdate);

            $this->flashBag->add('warning', $this->translator->trans('support_person.invalid_start_date', [
                'person_fullname' => $supportPerson->getPerson()->getFullname(),
            ], 'app'));
        }

        return $supportPerson;
    }
}
