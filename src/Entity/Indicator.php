<?php

namespace App\Entity;

use App\Repository\IndicatorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IndicatorRepository::class)
 */
class Indicator
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbPeople;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbGroups;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbSupportsGroup;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbSupportsPeople;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbEvaluations;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbNotes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbRdvs;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbDocuments;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbContributions;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbConnections;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getNbPeople(): ?int
    {
        return $this->nbPeople;
    }

    public function setNbPeople(?int $nbPeople): self
    {
        $this->nbPeople = $nbPeople;

        return $this;
    }

    public function getNbGroups(): ?int
    {
        return $this->nbGroups;
    }

    public function setNbGroups(?int $nbGroups): self
    {
        $this->nbGroups = $nbGroups;

        return $this;
    }

    public function getNbSupportsGroup(): ?int
    {
        return $this->nbSupportsGroup;
    }

    public function setNbSupportsGroup(?int $nbSupportsGroup): self
    {
        $this->nbSupportsGroup = $nbSupportsGroup;

        return $this;
    }

    public function getNbSupportsPeople(): ?int
    {
        return $this->nbSupportsPeople;
    }

    public function setNbSupportsPeople(?int $nbSupportsPeople): self
    {
        $this->nbSupportsPeople = $nbSupportsPeople;

        return $this;
    }

    public function getNbEvaluations(): ?int
    {
        return $this->nbEvaluations;
    }

    public function setNbEvaluations(?int $nbEvaluations): self
    {
        $this->nbEvaluations = $nbEvaluations;

        return $this;
    }

    public function getNbNotes(): ?int
    {
        return $this->nbNotes;
    }

    public function setNbNotes(?int $nbNotes): self
    {
        $this->nbNotes = $nbNotes;

        return $this;
    }

    public function getNbRdvs(): ?int
    {
        return $this->nbRdvs;
    }

    public function setNbRdvs(?int $nbRdvs): self
    {
        $this->nbRdvs = $nbRdvs;

        return $this;
    }

    public function getNbDocuments(): ?int
    {
        return $this->nbDocuments;
    }

    public function setNbDocuments(?int $nbDocuments): self
    {
        $this->nbDocuments = $nbDocuments;

        return $this;
    }

    public function getNbContributions(): ?int
    {
        return $this->nbContributions;
    }

    public function setNbContributions(?int $nbContributions): self
    {
        $this->nbContributions = $nbContributions;

        return $this;
    }

    public function getNbConnections(): ?int
    {
        return $this->nbConnections;
    }

    public function setNbConnections(?int $nbConnections): self
    {
        $this->nbConnections = $nbConnections;

        return $this;
    }
}
