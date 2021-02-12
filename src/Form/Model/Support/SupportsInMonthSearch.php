<?php

namespace App\Form\Model\Support;

use App\Form\Model\Organization\ReferentServiceDeviceSearchTrait;

class SupportsInMonthSearch
{
    use ReferentServiceDeviceSearchTrait;

    /**
     * @var \DateTimeInterface|null
     */
    private $date;

    /** @var bool */
    private $export;

    public function __construct()
    {
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

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
