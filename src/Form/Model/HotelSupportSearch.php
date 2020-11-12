<?php

namespace App\Form\Model;

use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;
use Doctrine\Common\Collections\ArrayCollection;

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
     * @var array
     */
    private $levelSupport;

    /**
     * @var int|null
     */
    private $departmentAnchor;

    /**
     * @var array
     */
    private $endSupportReasons;

    /**
     * @var ArrayCollection
     */
    private $hotels;

    /**
     * @var bool
     */
    private $export;

    public function __construct()
    {
        $this->hotels = new ArrayCollection();
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(?string $fullname): self
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

    public function setSupportDates(?int $supportDates): self
    {
        $this->supportDates = $supportDates;

        return $this;
    }

    public function setDiagOrSupport(?int $diagOrSupport): self
    {
        $this->diagOrSupport = $diagOrSupport;

        return $this;
    }

    public function getLevelSupport(): ?array
    {
        return $this->levelSupport;
    }

    public function setLevelSupport(?array $levelSupport): self
    {
        $this->levelSupport = $levelSupport;

        return $this;
    }

    public function getDepartmentAnchor(): ?int
    {
        return $this->departmentAnchor;
    }

    public function setDepartmentAnchor(?int $departmentAnchor): self
    {
        $this->departmentAnchor = $departmentAnchor;

        return $this;
    }

    public function getEndSupportReasons(): ?array
    {
        return $this->endSupportReasons;
    }

    public function setEndSupportReasons(?array $endSupportReasons): self
    {
        $this->endSupportReasons = $endSupportReasons;

        return $this;
    }

    public function getHotels(): ?ArrayCollection
    {
        return $this->hotels;
    }

    public function getHotelsToString(): array
    {
        $hotels = [];

        foreach ($this->hotels as $hotel) {
            $hotels[] = $hotel->getname();
        }

        return $hotels;
    }

    public function setHotels(?ArrayCollection $hotels): self
    {
        $this->hotels = $hotels;

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
