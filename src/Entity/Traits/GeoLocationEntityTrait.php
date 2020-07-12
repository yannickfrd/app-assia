<?php

namespace App\Entity\Traits;

trait GeoLocationEntityTrait
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $locationId;

    /**
     * @ORM\Column(type="float",nullable=true)
     */
    private $lat;

    /**
     * @ORM\Column(type="float",nullable=true)
     */
    private $lon;

    public function getLocationId(): ?string
    {
        return $this->locationId;
    }

    public function setLocationId(?string $locationId): self
    {
        $this->locationId = $locationId;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(?float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(?float $lon): self
    {
        $this->lon = $lon;

        return $this;
    }
}
