<?php

namespace App\Entity;

use App\Utils\Phone;
use Symfony\Component\Validator\Constraints as Assert;

class ServiceSearch
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     * @Assert\Email(message="Email invalide.")
     */
    private $email;

    /**
     * @var string|null
     * @Assert\Regex(pattern="^0[1-9]([-._/ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone;
    /**
     * @var string|null
     */
    private $city;

    /**
     * @var int|null
     */
    private $pole;

    public function __construct()
    { }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {

        $this->phone = Phone::formatPhone($phone);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {

        $this->city = $city;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPole(): ?Pole
    {
        return $this->pole;
    }

    public function setPole(?Pole $pole): self
    {
        $this->pole = $pole;

        return $this;
    }
}
