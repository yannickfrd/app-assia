<?php

namespace App\Form\Model\People;

class DuplicatedPeopleSearch
{
    /**
     * @var bool
     */
    private $lastname = true;

    /**
     * @var bool
     */
    private $firstname = true;

    /**
     * @var bool
     */
    private $birthdate = true;

    public function getLastname(): bool
    {
        return $this->lastname;
    }

    public function setLastname(bool $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): bool
    {
        return $this->firstname;
    }

    public function setFirstname(bool $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getBirthdate(): bool
    {
        return $this->birthdate;
    }

    public function setBirthdate(bool $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }
}
