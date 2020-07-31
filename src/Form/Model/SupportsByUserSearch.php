<?php

namespace App\Form\Model;

use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;

class SupportsByUserSearch
{
    use ReferentServiceDeviceSearchTrait;

    /**
     * @var bool|null
     */
    protected $send = true;

    public function getSend(): ?bool
    {
        return $this->send;
    }

    public function setSend(?bool $send): self
    {
        $this->send = $send;

        return $this;
    }
}
