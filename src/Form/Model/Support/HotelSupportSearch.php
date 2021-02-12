<?php

namespace App\Form\Model\Support;

use Doctrine\Common\Collections\ArrayCollection;

class HotelSupportSearch extends SupportSearch
{
    /** @var array */
    private $levelSupport;

    /** @var int|null */
    private $departmentAnchor;

    /** @var array */
    private $endSupportReasons;

    /** @var ArrayCollection */
    private $hotels;

    public function __construct()
    {
        $this->hotels = new ArrayCollection();
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
}
