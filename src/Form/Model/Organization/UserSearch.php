<?php

namespace App\Form\Model\Organization;

use App\Form\Utils\Choices;
use App\Service\Phone;
use Doctrine\Common\Collections\ArrayCollection;

class UserSearch
{
    /** @var string|null */
    private $firstname;

    /** @var string|null */
    private $lastname;

    private $username;

    /** @var array */
    private $status;

    /** @var int|null */
    private $serviceUser;

    /** @var string|null */
    private $phone;

    /** @var string|null */
    private $email;

    /** @var ArrayCollection */
    private $services;

    /** @var ArrayCollection */
    private $poles;

    /** @var int|null */
    private $disabled = Choices::ACTIVE;

    /** @var bool */
    private $export;

    public function __construct()
    {
        $this->poles = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function getServiceUser(): ?int
    {
        return $this->serviceUser;
    }

    public function setServiceUser(?int $serviceUser): self
    {
        $this->serviceUser = $serviceUser;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = Phone::formatPhone($phone);

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getServices(): ?ArrayCollection
    {
        return $this->services;
    }

    public function setServices(?ArrayCollection $services): self
    {
        $this->services = $services;

        return $this;
    }

    public function getPoles(): ?ArrayCollection
    {
        return $this->poles;
    }

    public function setPoles(?ArrayCollection $poles): self
    {
        $this->poles = $poles;

        return $this;
    }

    public function getDisabled(): ?int
    {
        return $this->disabled;
    }

    public function setDisabled(?int $disabled): self
    {
        $this->disabled = $disabled;

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
