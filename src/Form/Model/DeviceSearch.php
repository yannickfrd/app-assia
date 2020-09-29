<?php

namespace App\Form\Model;

use App\Entity\Pole;
use App\Entity\Service;

class DeviceSearch
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var Service|null
     */
    private $service;

    /**
     * @var Pole|null
     */
    private $pole;

    /**
     * @var int|null
     */
    private $disabled;

    public function __construct()
    {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

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

    public function getDisabled(): ?int
    {
        return $this->disabled;
    }

    public function setDisabled(?int $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }
}
