<?php

namespace App\Service\SupportGroup;

use App\Entity\Organization\Device;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;

class HotelSupportService
{
    /**
     * Met à jour le suivi social hôtel.
     */
    public function updateSupportGroup(SupportGroup $supportGroup): SupportGroup
    {
        $supportGroup->setStatus($this->getStatus($supportGroup));

        if (Choices::YES === $supportGroup->getService()->getCoefficient() && $supportGroup->getHotelSupport()
            && Device::HOTEL_SUPPORT === $supportGroup->getDevice()->getCode()) {
            $supportGroup->setCoefficient($this->getCoeffSupport($supportGroup));
        }

        if ($supportGroup->getPlaceGroups()->count() > 0) {
            $this->updateLocation($supportGroup);
        }

        $this->updateSupportPeople($supportGroup);

        return $supportGroup;
    }

    protected function getStatus(SupportGroup $supportGroup): int
    {
        if ($supportGroup->getEndDate()) {
            return SupportGroup::STATUS_ENDED;
        }

        if ($supportGroup->getStartDate()) {
            return SupportGroup::STATUS_IN_PROGRESS;
        }

        return $supportGroup->getStatus();
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
    protected function updateLocation(SupportGroup $supportGroup): void
    {
        $place = $supportGroup->getPlaceGroups()->first()->getPlace();

        if (null === $place) {
            return;
        }

        $supportGroup
            ->setAddress($place->getAddress())
            ->setCity($place->getCity())
            ->setZipcode($place->getZipcode())
            ->setCommentLocation($place->getCommentLocation())
            ->setLocationId($place->getLocationId())
            ->setLat($place->getLat())
            ->setLon($place->getLon());
    }

    /**
     * Donne le coefficient du suivi hôtel.
     */
    protected function getCoeffSupport(SupportGroup $supportGroup): float
    {
        if (Choices::NO === $supportGroup->getService()->getCoefficient()) {
            return SupportGroup::DEFAULT_COEFFICIENT;
        }

        $levelSupport = $supportGroup->getHotelSupport()->getLevelSupport();

        // Si accompagnement en complémentarité : coeff. 0,5
        if (3 === $levelSupport) {
            return SupportGroup::COEFFICIENT_HALF;
        }
        // Si veille sociale : coeff. 0,3
        if (in_array($levelSupport, [4, 5], true)) {
            return SupportGroup::COEFFICIENT_THIRD;
        }
        // Sinon par défaut : coeff. 1
        return SupportGroup::DEFAULT_COEFFICIENT;
    }
}
