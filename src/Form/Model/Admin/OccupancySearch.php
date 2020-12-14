<?php

namespace App\Form\Model\Admin;

use App\Entity\Organization\Pole;
use App\Form\Model\Traits\DateSearchTrait;

class OccupancySearch
{
    use DateSearchTrait;

    /**
     * @var Pole|null
     */
    private $pole;

    /**
     * @var bool
     */
    private $export;

    public function getPole(): ?Pole
    {
        return $this->pole;
    }

    public function setPole(?Pole $pole): self
    {
        $this->pole = $pole;

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
