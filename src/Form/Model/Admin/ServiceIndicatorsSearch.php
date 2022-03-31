<?php

namespace App\Form\Model\Admin;

use App\Entity\Support\SupportGroup;
use App\Form\Model\Organization\ReferentServiceDeviceSearchTrait;
use App\Form\Model\Traits\DateSearchTrait;

class ServiceIndicatorsSearch
{
    use DateSearchTrait;
    use ReferentServiceDeviceSearchTrait;

    /** @var array */
    private $status = [];

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusToString(): array
    {
        $status = [];

        foreach ($this->status  as $value) {
            $status[] = SupportGroup::STATUS[$value];
        }

        return $status;
    }
}
