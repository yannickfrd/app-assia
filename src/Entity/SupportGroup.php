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
     * @ORM\OneToOne(targetEntity="App\Entity\SitSocial", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $sitSocial;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitFamilyGroup", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $sitFamilyGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitHousing", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $sitHousing;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitBudgetGroup", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $sitBudgetGroup;

    public function __construct()
    {
        $this->supportPerson = new ArrayCollection();
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
    public function getSupportPerson(): Collection
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

    public function getSitSocial(): ?SitSocial
    {
        return $this->sitSocial;
    }

    public function setSitSocial(SitSocial $sitSocial): self
    {
        $this->sitSocial = $sitSocial;

        // set the owning side of the relation if necessary
        if ($this !== $sitSocial->getSupportGroup()) {
            $sitSocial->setSupportGroup($this);
        }

        return $this;
    }

    public function getsitFamilyGroup(): ?sitFamilyGroup
    {
        return $this->sitFamilyGroup;
    }

    public function setsitFamilyGroup(sitFamilyGroup $sitFamilyGroup): self
    {
        $this->sitFamilyGroup = $sitFamilyGroup;

        // set the owning side of the relation if necessary
        if ($this !== $sitFamilyGroup->getSupportGroup()) {
            $sitFamilyGroup->setSupportGroup($this);
        }

        return $this;
    }

    public function getSitHousing(): ?SitHousing
    {
        return $this->sitHousing;
    }

    public function setSitHousing(SitHousing $sitHousing): self
    {
        $this->sitHousing = $sitHousing;

        // set the owning side of the relation if necessary
        if ($this !== $sitHousing->getSupportGroup()) {
            $sitHousing->setSupportGroup($this);
        }

        return $this;
    }

    public function getSitBudgetGroup(): ?SitBudgetGroup
    {
        return $this->sitBudgetGroup;
    }

    public function setSitBudgetGroup(SitBudgetGroup $sitBudgetGroup): self
    {
        $this->sitBudgetGroup = $sitBudgetGroup;

        // set the owning side of the relation if necessary
        if ($this !== $sitBudgetGroup->getSupportGroup()) {
            $sitBudgetGroup->setSupportGroup($this);
        }

        return $this;
    }
}
