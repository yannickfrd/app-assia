<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SupportGrpRepository")
 */
class SupportGrp
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="supportsGrpCreated")
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="supportsGrpUpdated")
     */
    private $updatedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportPers", mappedBy="supportGrp", orphanRemoval=true)
     */
    private $supportPers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="supportGrp")
     */
    private $service;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitSocial", mappedBy="supportGrp", cascade={"persist", "remove"})
     */
    private $sitSocial;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitFamilyGrp", mappedBy="supportGrp", cascade={"persist", "remove"})
     */
    private $sitFamilyGrp;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitHousing", mappedBy="supportGrp", cascade={"persist", "remove"})
     */
    private $sitHousing;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitBudgetGrp", mappedBy="supportGrp", cascade={"persist", "remove"})
     */
    private $sitBudgetGrp;

    public function __construct()
    {
        $this->supportPers = new ArrayCollection();
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
     * @return Collection|SupportPers[]
     */
    public function getSupportPers(): Collection
    {
        return $this->supportPers;
    }

    public function addSupportPers(SupportPers $supportPers): self
    {
        if (!$this->supportPers->contains($supportPers)) {
            $this->supportPers[] = $supportPers;
            $supportPers->setSupportGrp($this);
        }

        return $this;
    }

    public function removeSupportPers(SupportPers $supportPers): self
    {
        if ($this->supportPers->contains($supportPers)) {
            $this->supportPers->removeElement($supportPers);
            // set the owning side to null (unless already changed)
            if ($supportPers->getSupportGrp() === $this) {
                $supportPers->setSupportGrp(null);
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
        if ($this !== $sitSocial->getSupportGrp()) {
            $sitSocial->setSupportGrp($this);
        }

        return $this;
    }

    public function getsitFamilyGrp(): ?sitFamilyGrp
    {
        return $this->sitFamilyGrp;
    }

    public function setsitFamilyGrp(sitFamilyGrp $sitFamilyGrp): self
    {
        $this->sitFamilyGrp = $sitFamilyGrp;

        // set the owning side of the relation if necessary
        if ($this !== $sitFamilyGrp->getSupportGrp()) {
            $sitFamilyGrp->setSupportGrp($this);
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
        if ($this !== $sitHousing->getSupportGrp()) {
            $sitHousing->setSupportGrp($this);
        }

        return $this;
    }

    public function getSitBudgetGrp(): ?SitBudgetGrp
    {
        return $this->sitBudgetGrp;
    }

    public function setSitBudgetGrp(SitBudgetGrp $sitBudgetGrp): self
    {
        $this->sitBudgetGrp = $sitBudgetGrp;

        // set the owning side of the relation if necessary
        if ($this !== $sitBudgetGrp->getSupportGrp()) {
            $sitBudgetGrp->setSupportGrp($this);
        }

        return $this;
    }
}
