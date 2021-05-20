<?php

namespace App\Form\Model\Support\Traits;

use App\Form\Model\Support\PaymentSearch;

trait PaymentSearchTrait
{
    /**
     * @var array|null
     */
    private $type;

    /**
     * @var int
     */
    private $dateType = PaymentSearch::DATE_TYPE_DEFAULT;

    /**
     * @var bool
     */
    private $export;

    public function getType(): ?array
    {
        return $this->type;
    }

    public function setType(?array $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDateType(): ?int
    {
        return $this->dateType;
    }

    public function setDateType(?int $dateType): self
    {
        $this->dateType = $dateType;

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
