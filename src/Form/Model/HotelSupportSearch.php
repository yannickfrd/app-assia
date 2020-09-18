<?php

namespace App\Form\Model;

use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;

class HotelSupportSearch
{
    use DateSearchTrait;
    use ReferentServiceDeviceSearchTrait;

    public const SUPPORT_DATES = [
        1 => 'Début du suivi',
        2 => 'Fin du suivi',
        3 => 'Période de suivi',
    ];

    public const DIAG = 1;
    public const SUPPORT = 2;

    public const DIAG_OR_SUPPORT = [
        1 => 'Diagnostic',
        2 => 'Accompagnement',
    ];

    /**
     * @var string|null
     */
    private $fullname;

    /**
     * @var array
     */
    private $familyTypologies;

    /**
     * @var array
     */
    private $status;

    /**
     * @var int|null
     */
    private $supportDates;

    /**
     * @var int|null
     */
    private $diagOrSupport;

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

    public function getFamilyTypologies(): ?array
    {
        return $this->familyTypologies;
    }

    public function setFamilyTypologies(?array $familyTypologies): self
    {
        $this->familyTypologies = $familyTypologies;

        return $this;
    }

    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function setStatus(?array $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSupportDates(): ?int
    {
        return $this->supportDates;
    }

    public function setSupportDates(int $supportDates): self
    {
        $this->supportDates = $supportDates;

        return $this;
    }

    public function getDiagOrSupport(): ?int
    {
        return $this->diagOrSupport;
    }

    public function setDiagOrSupport(int $diagOrSupport): self
    {
        $this->diagOrSupport = $diagOrSupport;

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
