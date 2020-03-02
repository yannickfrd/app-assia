<?php

namespace App\Form\Model;

use App\Entity\User;
use App\Entity\SupportGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class SupportGroupSearch
{
    public const SUPPORT_DATES = [
        1 => "Début du suivi",
        2 => "Fin du suivi",
        3 => "Période de suivi"
    ];

    /**
     * @var string|null
     */
    private $fullname;

    /**
     * @var string|null
     */
    private $firstname;

    /**
     * @var date|null
     * @Assert\Date(message="Date de naissance invalide.")
     */
    private $birthdate;

    /**
     * @var int|null
     */
    private $role;

    /**
     * @var int|null
     */
    private $familyTypology;

    /**
     * @var int|null
     * @Assert\Range(min = 1, max = 9)
     */
    private $nbPeople;

    /**
     * @var Array
     */
    private $status;

    /**
     * @var int|null
     */
    private $supportDates;

    /**
     * @var date|null
     */
    private $startDate;

    /**
     * @var date|null
     */
    private $endDate;

    /**
     * @var User
     */
    private $referent;

    /**
     * @var ArrayCollection
     */
    private $services;

    /**
     * @var ArrayCollection
     */
    private $devices;

    /**
     * @var bool
     */
    private $export;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->devices = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

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

    public function getFamilyTypology(): ?int
    {
        return $this->familyTypology;
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

    /**
     *
     * @return Array|null
     */
    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function getStatusList()
    {
        return SupportGroup::STATUS[$this->status];
    }

    public function setStatus(?array $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getSupportDates(): ?int
    {
        return $this->supportDates;
    }

    public function setSupportDates(int $supportDates): self
    {
        $this->supportDates = $supportDates;

        return $this;
    }

    public function getSupportDatesList()
    {
        return self::SUPPORT_DATES[$this->supportDates];
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        if ($endDate) {
            $this->endDate = $endDate;
        }
        return $this;
    }

    public function getReferent(): ?User
    {
        return $this->referent;
    }

    public function setReferent(?User $referent): self
    {
        $this->referent = $referent;

        return $this;
    }


    /**
     *
     * @return ArrayCollection|null
     */
    public function getServices(): ?ArrayCollection
    {
        return $this->services;
    }

    public function setServices(?ArrayCollection $services): self
    {
        $this->services = $services;

        return $this;
    }

    /**
     *
     * @return ArrayCollection|null
     */
    public function getDevices(): ?ArrayCollection
    {
        return $this->devices;
    }

    public function setDevices(?ArrayCollection $devices): self
    {
        $this->devices = $devices;

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
