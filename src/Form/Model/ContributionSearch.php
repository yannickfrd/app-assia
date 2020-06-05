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

    /**
     * @var int|null
     */
    private $type;

    /**
     * @var bool
     */
    private $export;

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getExport(): ?bool
    {
        return $this->export;
    }

    public function setExport(bool $export): self
    {
        $this->export = $export;

        return $this;
    }
}
