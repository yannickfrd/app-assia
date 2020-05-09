<?php

namespace App\Form\Model;

use App\Entity\Service;

class Import
{
    /**
     * @var Service
     */
    private $service;

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }
}
