<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonAccommodationRepository")
 */
class PersonAccommodation
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
    private $endReason;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEndReason;

    // /**
    //  * @ORM\ManyToOne(targetEntity="App\Entity\Accommodation", inversedBy="PersonAccommodation")
    //  */
    // private $accommodation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notesCreated")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notesUpdated")
     */
    private $updatedBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupPeopleAccommodation", inversedBy="personAccommodations")
     */
    private $groupPeopleAccommodation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="personAccommodations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;


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

    public function getEndReason(): ?int
    {
        return $this->endReason;
    }

    public function getEndReasonList(): string
    {
        return GroupPeopleAccommodation::END_REASON[$this->endReason];
    }

    public function setEndReason(?int $endReason): self
    {
        $this->endReason = $endReason;

        return $this;
    }

    public function getCommentEndReason(): ?string
    {
        return $this->commentEndReason;
    }

    public function setCommentEndReason(?string $commentEndReason): self
    {
        $this->commentEndReason = $commentEndReason;

        return $this;
    }

    public function getAccommodation(): ?Accommodation
    {
        return $this->accommodation;
    }

    public function setAccommodation(?Accommodation $accommodation): self
    {
        $this->accommodation = $accommodation;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    public function getGroupPeopleAccommodation(): ?GroupPeopleAccommodation
    {
        return $this->groupPeopleAccommodation;
    }

    public function setGroupPeopleAccommodation(?GroupPeopleAccommodation $groupPeopleAccommodation): self
    {
        $this->groupPeopleAccommodation = $groupPeopleAccommodation;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }
}
