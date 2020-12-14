<?php

namespace App\Form\Model\Admin;

use App\Entity\Organization\Service;

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
