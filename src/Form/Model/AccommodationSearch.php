<?php

namespace App\Form\Model;

use App\Entity\Pole;
use Doctrine\Common\Collections\ArrayCollection;

class AccommodationSearch
{
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
     * @var date|null
     */
    private $startDate;

    /**
     * @var date|null
     */
    private $endDate;

    /**
     * @var string|null
     */
    private $city;

    /**
     * @var ArrayCollection
     */
    private $service;

    /**
     * @var ArrayCollection
     */
    private $device;

    /**
     * @var int|null
     */
    private $pole;

    /**
     * @var bool
     */
    private $export;

    public function __construct()
    {
        $this->service = new ArrayCollection();
    }

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        if ($endDate) {
            $this->endDate = $endDate;
        }

        return $this;
    }

    public function getService(): ?ArrayCollection
    {
        return $this->service;
    }

    public function setService(?ArrayCollection $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getDevice(): ?ArrayCollection
    {
        return $this->device;
    }

    public function setDevice(?ArrayCollection $device): self
    {
        $this->device = $device;

        return $this;
    }

    /**
     * @return int|null
     */
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
