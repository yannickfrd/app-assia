<?php

namespace App\Entity\People;

use App\Entity\Organization\Referent;
use App\Entity\Support\AccommodationGroup;
use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\People\PeopleGroupRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class PeopleGroup
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    public const CACHE_GROUP_SUPPORTS_KEY = 'people_group.supports';
    public const CACHE_GROUP_REFERENTS_KEY = 'people_group.referents';

    public const FAMILY_TYPOLOGY = [
        1 => 'Femme isolée',
        2 => 'Homme isolé',
        3 => 'Couple sans enfant',
        4 => 'Femme seule avec enfant(s)',
        5 => 'Homme seul avec enfant(s)',
        6 => 'Couple avec enfant(s)',
        7 => 'Groupe d\'adultes sans enfant',
        8 => 'Groupe d\'adultes avec enfant(s)',
        9 => 'Autre',
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
        9 => 'Autre',
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
     * @ORM\OneToMany(targetEntity="App\Entity\People\RolePerson", mappedBy="peopleGroup", orphanRemoval=true)
     * @Assert\All(constraints={
     *      @Assert\NotNull,
     * })
     * @Assert\Valid()
     */
    private $rolePeople;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\SupportGroup", mappedBy="peopleGroup", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $supports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organization\Referent", mappedBy="peopleGroup", orphanRemoval=true)
     */
    private $referents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\Document", mappedBy="peopleGroup")
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\AccommodationGroup", mappedBy="peopleGroup", orphanRemoval=true)
     */
    private $accommodationGroups;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $siSiaoId;

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
            $support->setPeopleGroup($this);
        }

        return $this;
    }

    public function removeSupport(SupportGroup $support): self
    {
        if ($this->supports->contains($support)) {
            $this->supports->removeElement($support);
            // set the owning side to null (unless already changed)
            if ($support->getPeopleGroup() === $this) {
                $support->setPeopleGroup(null);
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
            $referent->setPeopleGroup($this);
        }

        return $this;
    }

    public function removeReferent(Referent $referent): self
    {
        if ($this->referents->contains($referent)) {
            $this->referents->removeElement($referent);
            // set the owning side to null (unless already changed)
            if ($referent->getPeopleGroup() === $this) {
                $referent->setPeopleGroup(null);
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
            $document->setPeopleGroup($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            // set the owning side to null (unless already changed)
            if ($document->getPeopleGroup() === $this) {
                $document->setPeopleGroup(null);
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
            $accommodationGroup->setPeopleGroup($this);
        }

        return $this;
    }

    public function removeAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if ($this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups->removeElement($accommodationGroup);
            // set the owning side to null (unless already changed)
            if ($accommodationGroup->getPeopleGroup() === $this) {
                $accommodationGroup->setPeopleGroup(null);
            }
        }

        return $this;
    }

    public function getSiSiaoId(): ?int
    {
        return $this->siSiaoId;
    }

    public function setSiSiaoId(?int $siSiaoId): self
    {
        $this->siSiaoId = $siSiaoId;

        return $this;
    }
}
