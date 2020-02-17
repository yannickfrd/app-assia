<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SupportGroupRepository")
 */
class SupportGroup
{
    public const STATUS = [
        1 => "À venir",
        2 => "En cours",
        3 => "Suspendu",
        4 => "Terminé",
        5 => "Autre"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotNull(message="La date de début ne doit pas être vide.")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="Le statut doit être renseigné.")
     * @Assert\Range(min = 1, max = 5, minMessage="Le statut doit être renseigné.",  maxMessage="Le statut doit être renseigné.")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="referentSupport")
     */
    private $referent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="referent2Support")
     */
    private $referent2;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $agreement;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupPeople", inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupPeople;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="supportsGroupCreated")
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="supportsGroupUpdated")
     */
    private $updatedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportPerson", mappedBy="supportGroup", orphanRemoval=true)
     */
    private $supportPerson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="supportGroup")
     */
    private $service;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="supportGroup")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rdv", mappedBy="supportGroup")
     */
    private $rdvs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="supportGroup")
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccommodationGroup", mappedBy="supportGroup", orphanRemoval=true)
     */
    private $accommodationGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationGroup", mappedBy="supportGroup")
     */
    private $evaluationsGroup;

    public function __construct()
    {
        $this->supportPerson = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->rdvs = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->accommodationGroups = new ArrayCollection();
        $this->evaluationsGroup = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getStatusType()
    {
        return self::STATUS[$this->status];
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

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

    public function getReferent2(): ?User
    {
        return $this->referent2;
    }

    public function setReferent2(?User $referent2): self
    {
        $this->referent2 = $referent2;

        return $this;
    }

    public function getAgreement(): ?bool
    {
        return $this->agreement;
    }

    public function setAgreement(?bool $agreement): self
    {
        $this->agreement = $agreement;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

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
     * @return Collection|SupportPerson[]
     */
    public function getSupportPerson(): ?Collection
    {
        return $this->supportPerson;
    }

    public function addSupportPerson(SupportPerson $supportPerson): self
    {
        if (!$this->supportPerson->contains($supportPerson)) {
            $this->supportPerson[] = $supportPerson;
            $supportPerson->setSupportGroup($this);
        }

        return $this;
    }

    public function removeSupportPerson(SupportPerson $supportPerson): self
    {
        if ($this->supportPerson->contains($supportPerson)) {
            $this->supportPerson->removeElement($supportPerson);
            // set the owning side to null (unless already changed)
            if ($supportPerson->getSupportGroup() === $this) {
                $supportPerson->setSupportGroup(null);
            }
        }

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    /**
     * @return Collection|Note[]
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setSupportGroup($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getSupportGroup() === $this) {
                $note->setSupportGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Rdv[]
     */
    public function getRdvs(): Collection
    {
        return $this->rdvs;
    }

    public function addRdv(Rdv $rdv): self
    {
        if (!$this->rdvs->contains($rdv)) {
            $this->rdvs[] = $rdv;
            $rdv->setSupportGroup($this);
        }

        return $this;
    }

    public function removeRdv(Rdv $rdv): self
    {
        if ($this->rdvs->contains($rdv)) {
            $this->rdvs->removeElement($rdv);
            // set the owning side to null (unless already changed)
            if ($rdv->getSupportGroup() === $this) {
                $rdv->setSupportGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setSupportGroup($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            // set the owning side to null (unless already changed)
            if ($document->getSupportGroup() === $this) {
                $document->setSupportGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AccommodationGroup[]
     */
    public function getAccommodationGroups(): Collection
    {
        return $this->accommodationGroups;
    }

    public function addAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if (!$this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups[] = $accommodationGroup;
            $accommodationGroup->setSupportGroup($this);
        }

        return $this;
    }

    public function removeAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if ($this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups->removeElement($accommodationGroup);
            // set the owning side to null (unless already changed)
            if ($accommodationGroup->getSupportGroup() === $this) {
                $accommodationGroup->setSupportGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EvaluationGroup[]
     */
    public function getEvaluationsGroup(): Collection
    {
        return $this->evaluationsGroup;
    }

    public function addEvaluationGroup(EvaluationGroup $evaluationGroup): self
    {
        if (!$this->evaluationsGroup->contains($evaluationGroup)) {
            $this->evaluationsGroup[] = $evaluationGroup;
            $evaluationGroup->setSupportGroup($this);
        }

        return $this;
    }

    public function removeEvaluationGroup(EvaluationGroup $evaluationGroup): self
    {
        if ($this->evaluationsGroup->contains($evaluationGroup)) {
            $this->evaluationsGroup->removeElement($evaluationGroup);
            // set the owning side to null (unless already changed)
            if ($evaluationGroup->getSupportGroup() === $this) {
                $evaluationGroup->setSupportGroup(null);
            }
        }

        return $this;
    }
}
