<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupPeopleRepository")
 */
class GroupPeople
{
    public const FAMILY_TYPOLOGY = [
        NULL => "",
        1 => "Femme seule",
        2 => "Homme seul",
        3 => "Couple sans enfant",
        4 => "Femme seule avec enfant(s)",
        5 => "Homme seul avec enfant(s)",
        6 => "Couple avec enfant(s)",
        7 => "Autre"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $familyTypology;

    /**
     * @ORM\Column(type="integer")
     * @Assert\Range(min = 1, max = 9)
     */
    private $nbPeople;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $updatedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RolePerson", mappedBy="groupPeople")
     */
    private $rolePerson;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SocialSupport", mappedBy="groupPeople")
     */
    private $socialSupports;
    
    public function __construct()
    {
        $this->socialSupports = new ArrayCollection();
        $this->rolePerson = new ArrayCollection();
        $this->createdAt = new \Datetime();
        $this->updatedAt = new \Datetime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFamilyTypology(): ?int
    {
        return $this->familyTypology;
    }

    public function getFamilyTypologyType(): string
    {
        return self::FAMILY_TYPOLOGY[$this->familyTypology];
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

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): self
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
    
    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?int $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
    
    /**
     * @return Collection|SocialSupport[]
     */
    public function getSocialSupports(): Collection
    {
        return $this->socialSupports;
    }

    public function addSocialSupport(SocialSupport $socialSupport): self
    {
        if (!$this->socialSupports->contains($socialSupport)) {
            $this->socialSupports[] = $socialSupport;
            $socialSupport->setGroupPeople($this);
        }

        return $this;
    }

    public function removeSocialSupport(SocialSupport $socialSupport): self
    {
        if ($this->socialSupports->contains($socialSupport)) {
            $this->socialSupports->removeElement($socialSupport);
            // set the owning side to null (unless already changed)
            if ($socialSupport->getGroupPeople() === $this) {
                $socialSupport->setGroupPeople(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RolePerson[]
     */
    public function getrolePerson(): Collection
    {
        return $this->rolePerson;
    }

    public function addRolePerson(RolePerson $rolePerson): self
    {
        if (!$this->rolePerson->contains($rolePerson)) {
            $this->rolePerson[] = $rolePerson;
            $rolePerson->setGroupPeople($this);
        }

        return $this;
    }

    public function removeRolePerson(RolePerson $rolePerson): self
    {
        if ($this->rolePerson->contains($rolePerson)) {
            $this->rolePerson->removeElement($rolePerson);
            // set the owning side to null (unless already changed)
            if ($rolePerson->getGroupPeople() === $this) {
                $rolePerson->setGroupPeople(null);
            }
        }

        return $this;
    }
}
