<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class GroupPeopleSearch
{
    public const ROLE = [
        1 => "Demandeur",
        2 => "Conjoint·e",
        3 => "Époux/se",
        4 => "Enfant",
        5 => "Membre de la famille",
        6 => "Parent isolé",
        7 => "Personne isolée",
        8 => "Autre"
    ];

    public const HEAD = [
        1 => "Oui",
        0 => "Non",
    ];

    public const FAMILY_TYPOLOGY = [
        1 => "Femme seule",
        2 => "Homme seul",
        3 => "Couple sans enfant",
        4 => "Femme seule avec enfant(s)",
        5 => "Homme seul avec enfant(s)",
        6 => "Couple avec enfant(s)",
        7 => "Autre"
    ];

    /**
     * @var string|null
     */
    private $lastname;

    /**
     * @var string|null
     */
    private $firstname;

    /**
     * @var date|null
     */
    private $birthdate;

    /**
     * @var int|null
     */
    private $role;

    /**
     * @var bool|null
     */
    private $head;

    /**
     * @var int|null
     */
    private $familyTypology;

    /**
     * @var int|null
     * @Assert\Range(min = 1, max = 9)
     */
    private $nbPeople;


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

    public function getGenderType() 
    {
        return self::GENDER[$this->gender];
    }
    
    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function listRole() 
    {
        return self::ROLE[$this->role];
    }    

    public function getHead(): ?bool
    {
        return $this->head;
    }

    public function setHead(bool $head): self
    {
        $this->head = $head;

        return $this;
    }

    public function listHead() 
    {
        return self::HEAD[$this->head];
    }    

    public function getFamilyTypology(): ?int
    {
        return $this->familyTypology;
    }

    public function getFamilyTypologyType(): string
    {
        return self::FAMILY_TYPOLOGY[$this->familyTypology];
    }

    public function setFamilyTypology(int $familyTypology): self
    {
        $this->familyTypology = $familyTypology;

        return $this;
    }  

    public function getNbPeople(): ?int
    {
        return $this->nbPeople;
    }

    public function setNbPeople(int $nbPeople): self
    {
        $this->nbPeople = $nbPeople;

        return $this;
    }    
}
