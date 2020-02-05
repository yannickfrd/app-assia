<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupPeopleAccommodationRepository")
 */
class GroupPeopleAccommodation
{
    public const END_REASON = [
        1 => "Fin du suivi",
        2 => "Changement de logement/hébergement",
        98 => "Autre",
        99 => "Non renseigné"
    ];

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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Accommodation", inversedBy="groupPeopleAccommodations")
     */
    private $accommodation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportGroup", inversedBy="groupPeopleAccommodations")
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportGroup;

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
     * @ORM\OneToMany(targetEntity="App\Entity\PersonAccommodation", mappedBy="groupPeopleAccommodation", orphanRemoval=true)
     */
    private $personAccommodations;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupPeople", inversedBy="groupPeopleAccommodations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupPeople;

    public function __construct()
    {
        $this->personAccommodations = new ArrayCollection();
    }


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
        return self::END_REASON[$this->endReason];
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

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

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

    /**
     * @return Collection|PersonAccommodation[]
     */
    public function getPersonAccommodations(): Collection
    {
        return $this->personAccommodations;
    }

    public function addPersonAccommodation(PersonAccommodation $personAccommodation): self
    {
        if (!$this->personAccommodations->contains($personAccommodation)) {
            $this->personAccommodations[] = $personAccommodation;
            $personAccommodation->setGroupPeopleAccommodation($this);
        }

        return $this;
    }

    public function removePersonAccommodation(PersonAccommodation $personAccommodation): self
    {
        if ($this->personAccommodations->contains($personAccommodation)) {
            $this->personAccommodations->removeElement($personAccommodation);
            // set the owning side to null (unless already changed)
            if ($personAccommodation->getGroupPeopleAccommodation() === $this) {
                $personAccommodation->setGroupPeopleAccommodation(null);
            }
        }

        return $this;
    }

    public function getGroupPeople(): ?GroupPeople
    {
        return $this->groupPeople;
    }

    public function setGroupPeople(?GroupPeople $groupPeople): self
    {
        $this->groupPeople = $groupPeople;

        return $this;
    }
}
