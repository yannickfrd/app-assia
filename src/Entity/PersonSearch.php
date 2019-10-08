<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class PersonSearch
{
    public const GENDER = [
        1 => "Femme",
        2 => "Homme",
        3 => "Non renseigné"
    ];

    private $lastname;
    private $firstname;
    private $usename;
    private $birthdate;
    private $gender;

    /**
     * @Assert\Regex(pattern="^0[1-9]([-._ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone;

    /**
     * @Assert\Email(message="L'adresse email n'est pas valide.")
     */
    private $email;

    public function __construct()
    {
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

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getUsename(): ?string
    {
        return $this->usename;
    }

    public function setUsename(string $usename): self
    {
        $this->usename = $usename;

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

    public function getAge(): ?int
    {
        if ($this->birthdate) {
            $now   = new \DateTime();
            return $this->birthdate->diff($now)->y;
        } else {
            return NULL;
        }
    }

    public function setAge(int $age): self
    {
        $now = new \DateTime();
        $interval = $now->diff($this->birthdate);
        $days = $interval->days;
        $this->age = floor($days / 365.225);

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGenderType() 
    {
        return self::GENDER[$this->gender];
    }
    

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

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
}
