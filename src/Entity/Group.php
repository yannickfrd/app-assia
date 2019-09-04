<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 */
class Group
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $familyTypology;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbrPeople;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updateDate;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Person", inversedBy="groups")
     */
    private $people;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SocialSupport", mappedBy="groupPeople")
     */
    private $socialSupports;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->socialSupports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFamilyTypology(): ?string
    {
        return $this->familyTypology;
    }

    public function setFamilyTypology(string $familyTypology): self
    {
        $this->familyTypology = $familyTypology;

        return $this;
    }

    public function getNbrPeople(): ?int
    {
        return $this->nbrPeople;
    }

    public function setNbrPeople(int $nbrPeople): self
    {
        $this->nbrPeople = $nbrPeople;

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

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeInterface $creationDate): self
    {
        $this->creationDate = $creationDate;

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
     * @return Collection|Person[]
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people[] = $person;
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->people->contains($person)) {
            $this->people->removeElement($person);
        }

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
}
