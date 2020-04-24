<?php

namespace App\Form\Model;

use App\Entity\Pole;
use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;

class AccommodationSearch
{
    use DateSearchTrait;
    use ReferentServiceDeviceSearchTrait;

    public const ACCOMMODATION_DATES = [
        1 => 'Ouverture',
        2 => 'Fermeture',
        3 => "Période d'activité",
    ];

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var int|null
     */
    private $placesNumber;

    /**
     * @var int|null
     */
    private $supportDates;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var Pole|null
     */
    private $pole;

    /**
     * @var bool
     */
    private $export;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getplacesNumber(): ?int
    {
        return $this->placesNumber;
    }

    public function setPlacesNumber(?int $placesNumber): self
    {
        $this->placesNumber = $placesNumber;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getSupportDates(): ?int
    {
        return $this->supportDates;
    }

    public function setSupportDates(int $supportDates): self
    {
        $this->supportDates = $supportDates;

        return $this;
    }

    public function getPole(): ?Pole
    {
        return $this->pole;
    }

    public function setPole(?Pole $pole): self
    {
        $this->pole = $pole;

        return $this;
    }

    public function getExport(): ?bool
    {
        return $this->export;
    }

    public function setExport(bool $export): self
    {
        $this->export = $export;

        return $this;
    }
}
