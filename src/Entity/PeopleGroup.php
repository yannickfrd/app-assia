<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PeopleGroupRepository")
 */
class PeopleGroup
{
    public const TYPOLOGY = [
        1 => "Femme seule",
        2 => "Homme seul",
        3 => "Couple sans enfant",
        4 => "Femme seule avec enfant(s)",
        5 => "Homme seul avec enfant(s)",
        6 => "Couple avec enfant(s)"
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
     */
    private $nbPeople;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateDate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SocialSupport", mappedBy="peopleGroup")
     */
    private $socialSupports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RolePerson", mappedBy="peopleGroup", cascade={"persist"})
     */
    private $rolePeople;

    public function __construct()
    {
        $this->socialSupports = new ArrayCollection();
        $this->rolePeople = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFamilyTypology(): ?int
    {
        return $this->familyTypology;
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

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

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

    public function getUpdateDate(): ?\DateTimeInterface
    {
        return $this->updateDate;
    }

    public function setUpdateDate(\DateTimeInterface $updateDate): self
    {
        $this->updateDate = $updateDate;

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
            $socialSupport->setPeopleGroup($this);
        }

        return $this;
    }

    public function removeSocialSupport(SocialSupport $socialSupport): self
    {
        if ($this->socialSupports->contains($socialSupport)) {
            $this->socialSupports->removeElement($socialSupport);
            // set the owning side to null (unless already changed)
            if ($socialSupport->getPeopleGroup() === $this) {
                $socialSupport->setPeopleGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RolePerson[]
     */
    public function getRolePeople(): Collection
    {
        return $this->rolePeople;
    }

    public function addRolePerson(RolePerson $rolePerson): self
    {
        if (!$this->rolePeople->contains($rolePerson)) {
            $this->rolePeople[] = $rolePerson;
            $rolePerson->setPeopleGroup($this);
        }

        return $this;
    }

    public function removeRolePerson(RolePerson $rolePerson): self
    {
        if ($this->rolePeople->contains($rolePerson)) {
            $this->rolePeople->removeElement($rolePerson);
            // set the owning side to null (unless already changed)
            if ($rolePerson->getPeopleGroup() === $this) {
                $rolePerson->setPeopleGroup(null);
            }
        }

        return $this;
    }

    public function getFamilyTypologyText() 
    {
        return self::TYPOLOGY[$this->familyTypology];
    }
}
