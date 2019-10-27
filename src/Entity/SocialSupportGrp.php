<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SocialSupportGrpRepository")
 */
class SocialSupportGrp
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
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupPeople", inversedBy="socialSupports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupPeople;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="socialSupportsGroupCreated")
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="socialSupportsGroupUpdated")
     */
    private $updatedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SocialSupportPers", mappedBy="socialSupportGrp", orphanRemoval=true)
     */
    private $socialSupportPers;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="socialSupportGrp")
     */
    private $department;

    public function __construct()
    {
        $this->socialSupportPers = new ArrayCollection();
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
     * @return Collection|SocialSupportPers[]
     */
    public function getSocialSupportPers(): Collection
    {
        return $this->socialSupportPers;
    }

    public function addSocialSupportPers(SocialSupportPers $socialSupportPers): self
    {
        if (!$this->socialSupportPers->contains($socialSupportPers)) {
            $this->socialSupportPers[] = $socialSupportPers;
            $socialSupportPers->setSocialSupportGrp($this);
        }

        return $this;
    }

    public function removeSocialSupportPers(SocialSupportPers $socialSupportPers): self
    {
        if ($this->socialSupportPers->contains($socialSupportPers)) {
            $this->socialSupportPers->removeElement($socialSupportPers);
            // set the owning side to null (unless already changed)
            if ($socialSupportPers->getSocialSupportGrp() === $this) {
                $socialSupportPers->setSocialSupportGrp(null);
            }
        }

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }
}
