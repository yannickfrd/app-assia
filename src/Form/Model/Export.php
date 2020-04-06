<?php

namespace App\Form\Model;

use App\Entity\SupportGroup;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class Export
{
    public const SUPPORT_DATES = [
        1 => 'Début du suivi',
        2 => 'Fin du suivi',
        3 => 'Période de suivi',
    ];

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
     * @var array
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
     * @var bool|null
     */
    private $evalAdm;

    /**
     * @var bool|null
     */
    private $evalBudget;

    /**
     * @var bool|null
     */
    private $evalFamily;

    /**
     * @var bool|null
     */
    private $evalHousing;

    /**
     * @var bool|null
     */
    private $evalProf;

    /**
     * @var bool|null
     */
    private $evalSocial;

    /**
     * @var bool|null
     */
    private $evalJustice;

    public function __construct()
    {
        $this->services = new ArrayCollection();
        $this->devices = new ArrayCollection();
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

    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function getStatusString()
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

    public function getServices(): ?ArrayCollection
    {
        return $this->services;
    }

    public function setServices(?ArrayCollection $services): self
    {
        $this->services = $services;

        return $this;
    }

    public function getDevices(): ?ArrayCollection
    {
        return $this->devices;
    }

    public function setDevices(?ArrayCollection $devices): self
    {
        $this->devices = $devices;

        return $this;
    }

    public function getEvalAdm(): ?bool
    {
        return $this->evalAdm;
    }

    public function setEvalAdm(bool $evalAdm): self
    {
        $this->evalAdm = $evalAdm;

        return $this;
    }

    public function getEvalBudget(): ?bool
    {
        return $this->evalBudget;
    }

    public function setEvalBudget(bool $evalBudget): self
    {
        $this->evalBudget = $evalBudget;

        return $this;
    }

    public function getEvalFamily(): ?bool
    {
        return $this->evalFamily;
    }

    public function setEvalFamily(bool $evalFamily): self
    {
        $this->evalFamily = $evalFamily;

        return $this;
    }

    public function getEvalHousing(): ?bool
    {
        return $this->evalHousing;
    }

    public function setEvalHousing(bool $evalHousing): self
    {
        $this->evalHousing = $evalHousing;

        return $this;
    }

    public function getEvalProf(): ?bool
    {
        return $this->evalProf;
    }

    public function setEvalProf(bool $evalProf): self
    {
        $this->evalProf = $evalProf;

        return $this;
    }

    public function getEvalSocial(): ?bool
    {
        return $this->evalSocial;
    }

    public function setEvalSocial(bool $evalSocial): self
    {
        $this->evalSocial = $evalSocial;

        return $this;
    }

    public function getEvalJustice(): ?bool
    {
        return $this->evalJustice;
    }

    public function setEvalJustice(bool $evalJustice): self
    {
        $this->evalJustice = $evalJustice;

        return $this;
    }
}
