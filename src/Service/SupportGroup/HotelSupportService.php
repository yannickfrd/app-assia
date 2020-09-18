<?php

namespace App\Service\SupportGroup;

use App\Entity\Device;
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

        if ($supportGroup->getDevice()->getId() == Device::HOTEL_SUPPORT) {
            $supportGroup->setCoefficient($this->getCoeffSupport($hotelSupport));
        }

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

    /**
     * Donne le coefficient du suivi hôtel.
     *
     * @return float
     */
    protected function getCoeffSupport(HotelSupport $hotelSupport): float
    {
        // Si accompagnement en complémentarité : coeff. 0,5
        if ($hotelSupport->getLevelSupport() == 3) {
            return SupportGroup::COEFFICIENT_HALF;
        }
        // Si veille sociale : coeff. 0,3
        if ($hotelSupport->getLevelSupport() == 4) {
            return SupportGroup::COEFFICIENT_THIRD;
        }
        // Sinon par défaut : coeff. 1
        return SupportGroup::COEFFICIENT_DEFAULT;
    }
}
