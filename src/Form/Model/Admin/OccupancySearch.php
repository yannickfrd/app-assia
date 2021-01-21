<?php

namespace App\Form\Model\Admin;

use App\Entity\Organization\Pole;
use App\Form\Model\Traits\DateSearchTrait;

class OccupancySearch
{
    use DateSearchTrait;

    /**
     * @var int
     */
    private $year;

    /**
     * @var Pole|null
     */
    private $pole;

    /**
     * @var bool
     */
    private $export;

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

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
