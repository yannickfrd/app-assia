<?php

namespace App\Form\Model\People;

class PersonSearch
{
    /**
     * @var string|null
     */
    private $lastname;

    /**
     * @var string|null
     */
    private $firstname;

    /**
     * @var \DateTimeInterface|null
     */
    private $birthdate;

    /**
     * @var string|null
     */
    private $siSiaoId;

    /**
     * @var bool
     */
    private $siSiaoSearch;

    public function __construct()
    {
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getSiSiaoId(): ?string
    {
        return $this->siSiaoId;
    }

    public function setSiSiaoId(?string $siSiaoId): self
    {
        $this->siSiaoId = $siSiaoId;

        return $this;
    }

    public function getSiSiaoSearch(): bool
    {
        return $this->siSiaoSearch;
    }

    public function setSiSiaoSearch(bool $siSiaoSearch): self
    {
        $this->siSiaoSearch = $siSiaoSearch;

        return $this;
    }
}
