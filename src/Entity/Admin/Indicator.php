<?php

namespace App\Entity\Admin;

use App\Repository\Admin\IndicatorRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IndicatorRepository::class)
 */
class Indicator
{
    public const CACHE_KEY = 'stats';

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
    private $nbCreatedPeople;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbCreatedGroups;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbCreatedSupportsGroup;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbUpdatedSupportsGroup;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbCreatedSupportsPeople;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbCreatedEvaluations;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbCreatedNotes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbUpdatedNotes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbCreatedRdvs;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbCreatedDocuments;

    /**
     * @ORM\Column(name="nb_created_contributions", type="integer", nullable=true)
     */
    private $nbCreatedPayments;

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

    public function getNbCreatedPeople(): ?int
    {
        return $this->nbCreatedPeople;
    }

    public function setNbCreatedPeople(?int $nbCreatedPeople): self
    {
        $this->nbCreatedPeople = $nbCreatedPeople;

        return $this;
    }

    public function getNbCreatedGroups(): ?int
    {
        return $this->nbCreatedGroups;
    }

    public function setNbCreatedGroups(?int $nbCreatedGroups): self
    {
        $this->nbCreatedGroups = $nbCreatedGroups;

        return $this;
    }

    public function getNbCreatedSupportsGroup(): ?int
    {
        return $this->nbCreatedSupportsGroup;
    }

    public function setNbCreatedSupportsGroup(?int $nbCreatedSupportsGroup): self
    {
        $this->nbCreatedSupportsGroup = $nbCreatedSupportsGroup;

        return $this;
    }

    public function getNbUpdatedSupportsGroup(): ?int
    {
        return $this->nbUpdatedSupportsGroup;
    }

    public function setNbUpdatedSupportsGroup(?int $nbUpdatedSupportsGroup): self
    {
        $this->nbUpdatedSupportsGroup = $nbUpdatedSupportsGroup;

        return $this;
    }

    public function getNbCreatedSupportsPeople(): ?int
    {
        return $this->nbCreatedSupportsPeople;
    }

    public function setNbCreatedSupportsPeople(?int $nbCreatedSupportsPeople): self
    {
        $this->nbCreatedSupportsPeople = $nbCreatedSupportsPeople;

        return $this;
    }

    public function getNbCreatedEvaluations(): ?int
    {
        return $this->nbCreatedEvaluations;
    }

    public function setNbCreatedEvaluations(?int $nbCreatedEvaluations): self
    {
        $this->nbCreatedEvaluations = $nbCreatedEvaluations;

        return $this;
    }

    public function getNbCreatedNotes(): ?int
    {
        return $this->nbCreatedNotes;
    }

    public function setNbCreatedNotes(?int $nbCreatedNotes): self
    {
        $this->nbCreatedNotes = $nbCreatedNotes;

        return $this;
    }

    public function getNbUpdatedNotes(): ?int
    {
        return $this->nbUpdatedNotes;
    }

    public function setNbUpdatedNotes(?int $nbUpdatedNotes): self
    {
        $this->nbUpdatedNotes = $nbUpdatedNotes;

        return $this;
    }

    public function getNbCreatedRdvs(): ?int
    {
        return $this->nbCreatedRdvs;
    }

    public function setNbCreatedRdvs(?int $nbCreatedRdvs): self
    {
        $this->nbCreatedRdvs = $nbCreatedRdvs;

        return $this;
    }

    public function getNbCreatedDocuments(): ?int
    {
        return $this->nbCreatedDocuments;
    }

    public function setNbCreatedDocuments(?int $nbCreatedDocuments): self
    {
        $this->nbCreatedDocuments = $nbCreatedDocuments;

        return $this;
    }

    public function getNbCreatedPayments(): ?int
    {
        return $this->nbCreatedPayments;
    }

    public function setNbCreatedPayments(?int $nbCreatedPayments): self
    {
        $this->nbCreatedPayments = $nbCreatedPayments;

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
