<?php

namespace App\Entity\Traits;

trait LocationEntityTrait
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(name="zipCode", type="string", length=10, nullable=true)
     */
    private $zipcode;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $commentLocation;

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    public function getCommentLocation(): ?string
    {
        return $this->commentLocation;
    }

    public function setCommentLocation(?string $commentLocation): self
    {
        $this->commentLocation = $commentLocation;

        return $this;
    }
}
