<?php

namespace App\Service\SupportGroup;

use App\Entity\Organization\Device;
use App\Entity\Support\HotelSupport;
use App\Entity\Support\SupportGroup;

class HotelSupportService
{
    /**
     * Met à jour le suivi social hôtel.
     */
    public function updateSupportGroup(SupportGroup $supportGroup): SupportGroup
    {
        $hotelSupport = $supportGroup->getHotelSupport();

        $supportGroup->setStatus($supportGroup->getEndDate() ? SupportGroup::STATUS_ENDED : SupportGroup::STATUS_IN_PROGRESS);

        if ($hotelSupport && Device::HOTEL_SUPPORT === $supportGroup->getDevice()->getId()) {
            $supportGroup->setCoefficient($this->getCoeffSupport($hotelSupport));
        }

        if ($supportGroup->getAccommodationGroups()->count() > 0) {
            $this->updateLocation($supportGroup);
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
            if (null === $supportPerson->getEndStatus()) {
                $supportPerson
                    ->setStatus($supportGroup->getStatus())
                    ->setStartDate($supportGroup->getStartDate())
                    ->setEndDate($supportGroup->getEndDate());
            }
        }
    }

    /**
     * Met à jour l'adresse du suivi via l'adresse du groupe de places.
     */
    protected function updateLocation(SupportGroup $supportGroup)
    {
        $accommodation = $supportGroup->getAccommodationGroups()->first()->getAccommodation();

        if (null === $accommodation) {
            return false;
        }

        $supportGroup
            ->setAddress($accommodation->getAddress())
            ->setCity($accommodation->getCity())
            ->setZipcode($accommodation->getZipcode())
            ->setCommentLocation($accommodation->getCommentLocation())
            ->setLocationId($accommodation->getLocationId())
            ->setLat($accommodation->getLat())
            ->setLon($accommodation->getLon());
    }

    /**
     * Donne le coefficient du suivi hôtel.
     *
     * @return float
     */
    protected function getCoeffSupport(HotelSupport $hotelSupport): float
    {
        // Si accompagnement en complémentarité : coeff. 0,5
        if (3 === $hotelSupport->getLevelSupport()) {
            return SupportGroup::COEFFICIENT_HALF;
        }
        // Si veille sociale : coeff. 0,3
        if (4 === $hotelSupport->getLevelSupport()) {
            return SupportGroup::COEFFICIENT_THIRD;
        }
        // Sinon par défaut : coeff. 1
        return SupportGroup::COEFFICIENT_DEFAULT;
    }
}
