<?php

namespace App\Form\Model\Organization;

use Doctrine\Common\Collections\ArrayCollection;

trait ReferentServiceDeviceSearchTrait
{
    /**
     * @var ArrayCollection
     */
    private $referents;

    /**
     * @var ArrayCollection
     */
    private $services;

    /**
     * @var ArrayCollection
     */
    private $subServices;

    /**
     * @var ArrayCollection
     */
    private $devices;

    public function __construct()
    {
        $this->referents = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->subServices = new ArrayCollection();
        $this->devices = new ArrayCollection();
    }

    public function getReferents(): ?ArrayCollection
    {
        return $this->referents;
    }

    public function getReferentsToString(): array
    {
        $referents = [];

        foreach ($this->referents as $referent) {
            $referents[] = $referent->getFullname();
        }

        return $referents;
    }

    public function setReferents(?ArrayCollection $referents): self
    {
        $this->referents = $referents;

        return $this;
    }

    public function getServices(): ?ArrayCollection
    {
        return $this->services;
    }

    public function getServicesToString(): array
    {
        $services = [];

        foreach ($this->services as $service) {
            $services[] = $service->getName();
        }

        return $services;
    }

    public function setServices(?ArrayCollection $services): self
    {
        $this->services = $services;

        return $this;
    }

    public function getSubServices(): ?ArrayCollection
    {
        return $this->subServices;
    }

    public function getSubServicesToString(): array
    {
        $subServices = [];

        foreach ($this->subServices as $subService) {
            $subServices[] = $subService->getName();
        }

        return $subServices;
    }

    public function setSubServices(?ArrayCollection $subServices): self
    {
        $this->subServices = $subServices;

        return $this;
    }

    public function getDevices(): ?ArrayCollection
    {
        return $this->devices;
    }

    public function getDevicesToString(): array
    {
        $devices = [];

        foreach ($this->devices as $device) {
            $devices[] = $device->getName();
        }

        return $devices;
    }

    public function setDevices(?ArrayCollection $devices): self
    {
        $this->devices = $devices;

        return $this;
    }
}
