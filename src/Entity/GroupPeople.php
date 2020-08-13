<?php

namespace App\Entity;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GroupPeopleRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class GroupPeople
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    public const FAMILY_TYPOLOGY = [
        1 => 'Femme isolée',
        2 => 'Homme isolé',
        3 => 'Couple sans enfant',
        4 => 'Femme seule avec enfant(s)',
        5 => 'Homme seul avec enfant(s)',
        6 => 'Couple avec enfant(s)',
        7 => 'Groupe d\'adultes sans enfant',
        8 => 'Groupe d\'adultes avec enfant(s)',
        97 => 'Autre',
    ];

    public const FAMILY_TYPO_MIN = [
        1 => 'FS',
        2 => 'HS',
        3 => 'C',
        4 => 'F+E',
        5 => 'H+E',
        6 => 'C+E',
        7 => 'G',
        8 => 'G+E',
        97 => 'Autre',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotNull(message="La typologie familiale doit être renseignée.")
     * @Assert\Range(min = 1, max = 9, minMessage="La typologie familiale doit être renseignée.",  maxMessage="La typologie familiale doit être renseignée.")
     */
    private $familyTypology;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\Range(min = 1, max = 99, minMessage="Le nombre de personnes doit être renseigné.",  maxMessage="Le nombre de personnes doit être renseigné.")
     * Groups("export")
     */
    private $nbPeople;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RolePerson", mappedBy="groupPeople", orphanRemoval=true)
     * @Assert\All(constraints={
     *      @Assert\NotNull,
     * })
     * @Assert\Valid()
     */
    private $rolePeople;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGroup", mappedBy="groupPeople", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $supports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Referent", mappedBy="groupPeople", orphanRemoval=true)
     */
    private $referents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="groupPeople")
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccommodationGroup", mappedBy="groupPeople", orphanRemoval=true)
     */
    private $accommodationGroups;

    public function __construct()
    {
        $this->supports = new ArrayCollection();
        $this->rolePeople = new ArrayCollection();
        $this->referents = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->accommodationGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFamilyTypology(): ?int
    {
        return $this->familyTypology;
    }

    /**
     * @Groups("export")
     */
    public function getFamilyTypologyToString(): string
    {
        return self::FAMILY_TYPOLOGY[$this->familyTypology];
    }

    public function getFamilyTypoMinToString(): string
    {
        return self::FAMILY_TYPO_MIN[$this->familyTypology];
    }

    public function setFamilyTypology(?int $familyTypology): self
    {
        $this->familyTypology = $familyTypology;

        return $this;
    }

    public function getNbPeople(): ?int
    {
        return $this->nbPeople;
    }

    public function setNbPeople(?int $nbPeople): self
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

    /**
     * @return Collection|SupportGroup[]
     */
    public function getSupports(): ?Collection
    {
        return $this->supports;
    }

    public function addSupport(SupportGroup $support): self
    {
        if (!$this->supports->contains($support)) {
            $this->supports[] = $support;
            $support->setGroupPeople($this);
        }

        return $this;
    }

    public function removeSupport(SupportGroup $support): self
    {
        if ($this->supports->contains($support)) {
            $this->supports->removeElement($support);
            // set the owning side to null (unless already changed)
            if ($support->getGroupPeople() === $this) {
                $support->setGroupPeople(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RolePerson[]
     */
    public function getRolePeople(): ?Collection
    {
        return $this->rolePeople;
    }

    public function addRolePerson(RolePerson $rolePerson): self
    {
        if (!$this->rolePeople->contains($rolePerson)) {
            $this->rolePeople[] = $rolePerson;
            $rolePerson->setGroupPeople($this);
        }

        return $this;
    }

    public function removeRolePerson(RolePerson $rolePerson): self
    {
        if ($this->rolePeople->contains($rolePerson)) {
            $this->rolePeople->removeElement($rolePerson);
            // set the owning side to null (unless already changed)
            if ($rolePerson->getGroupPeople() === $this) {
                $rolePerson->setGroupPeople(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Referent[]
     */
    public function getReferents(): ?Collection
    {
        return $this->referents;
    }

    public function addReferent(Referent $referent): self
    {
        if (!$this->referents->contains($referent)) {
            $this->referents[] = $referent;
            $referent->setGroupPeople($this);
        }

        return $this;
    }

    public function removeReferent(Referent $referent): self
    {
        if ($this->referents->contains($referent)) {
            $this->referents->removeElement($referent);
            // set the owning side to null (unless already changed)
            if ($referent->getGroupPeople() === $this) {
                $referent->setGroupPeople(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): ?Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setGroupPeople($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            // set the owning side to null (unless already changed)
            if ($document->getGroupPeople() === $this) {
                $document->setGroupPeople(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AccommodationGroup[]
     */
    public function getAccommodationGroups(): ?Collection
    {
        return $this->accommodationGroups;
    }

    public function addAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if (!$this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups[] = $accommodationGroup;
            $accommodationGroup->setGroupPeople($this);
        }

        return $this;
    }

    public function removeAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if ($this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups->removeElement($accommodationGroup);
            // set the owning side to null (unless already changed)
            if ($accommodationGroup->getGroupPeople() === $this) {
                $accommodationGroup->setGroupPeople(null);
            }
        }

        return $this;
    }
}
