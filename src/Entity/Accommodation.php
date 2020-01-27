<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccommodationRepository")
 */
class Accommodation
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endSituation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEndSituation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportPerson", inversedBy="accommodations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportPerson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Space", inversedBy="accommodations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $space;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
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
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndSituation(): ?int
    {
        return $this->endSituation;
    }

    public function setEndSituation(?int $endSituation): self
    {
        $this->endSituation = $endSituation;

        return $this;
    }

    public function getCommentEndSituation(): ?string
    {
        return $this->commentEndSituation;
    }

    public function setCommentEndSituation(?string $commentEndSituation): self
    {
        $this->commentEndSituation = $commentEndSituation;

        return $this;
    }

    public function getSupportPerson(): ?SupportPerson
    {
        return $this->supportPerson;
    }

    public function setSupportPerson(?SupportPerson $supportPerson): self
    {
        $this->supportPerson = $supportPerson;

        return $this;
    }

    public function getSpace(): ?Space
    {
        return $this->space;
    }

    public function setSpace(?Space $space): self
    {
        $this->space = $space;

        return $this;
    }
}
