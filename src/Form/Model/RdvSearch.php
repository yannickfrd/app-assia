<?php

namespace App\Form\Model;

use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\RdvSearchTrait;
use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;

class RdvSearch
{
    use RdvSearchTrait;
    use ReferentServiceDeviceSearchTrait;
    use DateSearchTrait;

    /**
     * @var string|null
     */
    private $fullname;

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }
}
