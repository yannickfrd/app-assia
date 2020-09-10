<?php

namespace App\Service\SupportGroup;

use App\Entity\HotelSupport;
use App\Entity\SupportGroup;

class HotelSupportService
{
    /**
     * Met à jour le suivi social hôtel.
     */
    public function updateSupportGroup(SupportGroup $supportGroup): SupportGroup
    {
        $hotelSupport = $supportGroup->getHotelSupport();

        $supportGroup
            ->setStatus($this->getHotelSupportStatus($hotelSupport))
            ->setStartDate($this->getHotelSupportStartDate($hotelSupport))
            ->setEndDate($this->getHotelSupportEndDate($hotelSupport));

        $this->updateSupportPeople($supportGroup);

        return $supportGroup;
    }

    /**
     * Met à jour les suivis individuelles des personnes en suivi hôtel.
     */
    protected function updateSupportPeople(SupportGroup $supportGroup): void
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (null == $supportPerson->getEndStatus()) {
                $supportPerson
                    ->setStatus($supportGroup->getStatus())
                    ->setStartDate($supportGroup->getStartDate())
                    ->setEndDate($supportGroup->getEndDate());
            }
        }
    }

    /**
     * Donne le statut du suivi hôtel.
     */
    protected function getHotelSupportStatus(HotelSupport $hotelSupport): int
    {
        if (($hotelSupport->getDiagEndDate() && $hotelSupport->getSupportStartDate() == null) || $hotelSupport->getSupportEndDate()) {
            return SupportGroup::STATUS_ENDED;
        }

        return SupportGroup::STATUS_IN_PROGRESS;
    }

    /**
     * Donne la date de début du suivi hôtel.
     */
    protected function getHotelSupportStartDate(HotelSupport $hotelSupport): ?\DateTimeInterface
    {
        if ($hotelSupport->getDiagStartDate()) {
            return $hotelSupport->getDiagStartDate();
        }

        return $hotelSupport->getSupportStartDate();
    }

    /**
     * Donne la date de fin du suivi hôtel.
     */
    protected function getHotelSupportEndDate(HotelSupport $hotelSupport): ?\DateTimeInterface
    {
        if ($hotelSupport->getSupportEndDate() || ($hotelSupport->getDiagEndDate() && $hotelSupport->getSupportStartDate() == null)) {
            return max([
                $hotelSupport->getDiagEndDate(),
                $hotelSupport->getSupportEndDate(),
            ]);
        }

        return null;
    }
}
