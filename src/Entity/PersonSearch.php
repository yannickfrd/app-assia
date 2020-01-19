<?php

namespace App\Entity;

use App\Service\Phone;
use App\Entity\Person;
use Symfony\Component\Validator\Constraints as Assert;

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

    private $usename;

    /**
     * @var date|null
     */
    private $birthdate;

    /**
     * @var int|null
     */
    private $gender;

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
     * @var bool
     */
    private $export;

    public function __construct()
    {
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
    public function getUsename(): ?string
    {
        return $this->usename;
    }

    public function setUsename(string $usename): self
    {
        $this->usename = $usename;

        return $this;
    }

    /**
     * @return date|null
     */
    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getAge(): ?int
    {
        if ($this->birthdate) {
            $now   = new \DateTime();
            return $this->birthdate->diff($now)->y;
        } else {
            return null;
        }
    }

    public function setAge(int $age): self
    {
        $now = new \DateTime();
        $interval = $now->diff($this->birthdate);
        $days = $interval->days;
        $this->age = floor($days / 365.25);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGenderList()
    {
        return Person::GENDER[$this->gender];
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
