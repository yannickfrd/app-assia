<?php

namespace App\Form\Model;

use App\Entity\Pole;
use App\Service\Phone;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class UserSearch
{
    /**
     * @var string|null
     */
    private $firstname;

    /**
     * @var string|null
     */
    private $lastname;

    private $username;

    /**
     * @var int|null
     */
    private $status;

    /**
     * @var int|null
     */
    private $serviceUser;

    /**
     * @var string|null
     * @Assert\Regex(pattern="^0[1-9]([-._/ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone;

    /**
     * @var string|null
     * @Assert\Email(message="Email invalide.")
     */
    private $email;

    /**
     * @var ArrayCollection
     */
    private $service;

    /**
     * @var int|null
     */
    private $pole;

    /**
     * @var bool|null
     */
    private $enabled = true;

    /**
     * @var bool
     */
    private $export;

    public function __construct()
    {
        $this->service = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getServiceUser(): ?int
    {
        return $this->serviceUser;
    }

    public function setServiceUser(?int $serviceUser): self
    {
        $this->serviceUser = $serviceUser;

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
     *
     * @return ArrayCollection|null
     */
    public function getService(): ?ArrayCollection
    {
        return $this->service;
    }

    public function setService(?ArrayCollection $service): self
    {
        $this->service = $service;

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

    /**
     * @return bool|null
     */
    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool|null
     */
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
