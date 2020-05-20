<?php

namespace App\Form\Model;

use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\ContributionSearchTrait;
use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;

class ContributionSearch
{
    use ContributionSearchTrait;
    use DateSearchTrait;
    use ReferentServiceDeviceSearchTrait;

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
