<?php

namespace App\Form\Model\Traits;

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
    private $devices;

    public function __construct()
    {
        $this->referents = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->devices = new ArrayCollection();
    }

    public function getReferents(): ?ArrayCollection
    {
        return $this->referents;
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

    public function setServices(?ArrayCollection $services): self
    {
        $this->services = $services;

        return $this;
    }

    public function getDevices(): ?ArrayCollection
    {
        return $this->devices;
    }

    public function setDevices(?ArrayCollection $devices): self
    {
        $this->devices = $devices;

        return $this;
    }
}
