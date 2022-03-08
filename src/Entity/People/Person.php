<?php

namespace App\Entity\People;

use App\Entity\Support\PlacePerson;
use App\Entity\Support\SupportPerson;
use App\Entity\Traits\ContactEntityTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\People\PersonRepository")
 * @UniqueEntity(
 *     fields={"firstname", "lastname", "birthdate"},
 *     errorPath="firstname",
 *     message="Cette personne existe déjà !")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Person
{
    use CreatedUpdatedEntityTrait;
    use ContactEntityTrait;
    use SoftDeleteableEntity;

    public const CACHE_PERSON_SUPPORTS_KEY = 'person.supports';

    public const GENDER_FEMALE = 1;
    public const GENDER_MALE = 2;

    public const GENDERS = [
        1 => 'Féminin',
        2 => 'Masculin',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const CIVILITIES = [
        1 => 'Madame',
        2 => 'Monsieur',
        97 => '',
        99 => '',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("show_person")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message = "Le nom ne doit pas être vide.")
     * @Assert\Length(
     * min=2,
     * max=50,
     * minMessage="Le nom est trop court ({{ limit }} caractères min).",
     * maxMessage="Le nom est trop long ({{ limit }} caractères max).")
     * @Groups({"export", "show_person"})
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message = "Le prénom ne doit pas être vide.")
     * @Assert\Length(
     * min=2,
     * max=50,
     * minMessage="Le prénom est trop court ({{ limit }} caractères min).",
     * maxMessage="Le prénom est trop long ({{ limit }} caractères max).")
     * @Groups({"export", "show_person"})
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $maidenName;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     * @Assert\Length(max=50,maxMessage="Le nom d'usage est trop long ({{ limit }} caractères max).")
     */
    private $usename;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\NotNull(message="La date de naissance ne doit pas être vide.")
     * @Groups("export")
     */
    private $birthdate;

    /**
     * @Assert\Range(
     * min = 0,
     * max = 90,
     * minMessage = "La date de naissance est incorrect.",
     * maxMessage = "La date de naissance est incorrect.")
     * @Groups("export")
     */
    private $age;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     * @Assert\NotNull(message="Le sexe doit être renseigné.")
     */
    private $gender;

    /**
     * @Groups("export").
     */
    private $genderToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $contactOtherPerson;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\People\RolePerson", mappedBy="person", orphanRemoval=true, cascade={"persist"})
     * @Assert\Valid()
     */
    private $rolesPerson;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\SupportPerson", mappedBy="person", orphanRemoval=true, cascade={"persist"})
     */
    private $supports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\PlacePerson", mappedBy="person", orphanRemoval=true)
     */
    private $placePeople;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $siSiaoId;

    private $slugger;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $soundexFirstname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $soundexLastname;

    public function __construct()
    {
        $this->rolesPerson = new ArrayCollection();
        $this->supports = new ArrayCollection();
        $this->placePeople = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->id;
        // return $this->lastname.' '.$this->firstname;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = strtoupper($lastname);

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = \ucfirst($firstname);

        return $this;
    }

    public function getSlug(): string
    {
        return strtolower((new AsciiSlugger())->slug($this->firstname.'-'.$this->lastname));
    }

    /**
     * @Groups({"show_person", "show_rdv"})
     */
    public function getFullname(): ?string
    {
        return $this->lastname.' '.$this->firstname;
    }

    public function getMaidenName(): ?string
    {
        return $this->maidenName;
    }

    public function setMaidenName(?string $maidenName): self
    {
        $this->maidenName = $maidenName;

        return $this;
    }

    public function getUsename(): ?string
    {
        return $this->usename;
    }

    public function setUsename(?string $usename): self
    {
        $this->usename = \ucfirst($usename);

        return $this;
    }

    public function getBirthdate(): ?\DateTimeInterface
    {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTimeInterface $birthdate): self
    {
        $this->birthdate = $birthdate;
        if ($birthdate) {
            $this->age = $birthdate->diff(new \DateTime())->y;
        }

        return $this;
    }

    public function getAge(): ?int
    {
        if ($this->birthdate) {
            return $this->birthdate->diff(new \DateTime())->y ?? 0;
        }

        return null;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(?int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGenderToString(): ?string
    {
        return self::GENDERS[$this->gender] ?? null;
    }

    public function getCivilityToString(): ?string
    {
        return self::CIVILITIES[$this->gender] ?? null;
    }

    public function getContactOtherPerson(): ?string
    {
        return $this->contactOtherPerson;
    }

    public function setContactOtherPerson(?string $contactOtherPerson): self
    {
        $this->contactOtherPerson = $contactOtherPerson;

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
     * @return Collection<RolePerson>|RolePerson[]|null
     */
    public function getRolesPerson(): ?Collection
    {
        return $this->rolesPerson;
    }

    public function addRolesPerson(RolePerson $rolesPerson): self
    {
        if (!$this->rolesPerson->contains($rolesPerson)) {
            $this->rolesPerson[] = $rolesPerson;
            $rolesPerson->setPerson($this);
        }

        return $this;
    }

    public function removeRolesPerson(RolePerson $rolesPerson): self
    {
        if ($this->rolesPerson->contains($rolesPerson)) {
            $this->rolesPerson->removeElement($rolesPerson);
            // set the owning side to null (unless already changed)
            if ($rolesPerson->getPerson() === $this) {
                $rolesPerson->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<SupportPerson>|SupportPerson[]|null
     */
    public function getSupports(): ?Collection
    {
        return $this->supports;
    }

    public function addSupport(SupportPerson $support): self
    {
        if (!$this->supports->contains($support)) {
            $this->supports[] = $support;
            $support->setPerson($this);
        }

        return $this;
    }

    public function removeSupport(SupportPerson $support): self
    {
        if ($this->supports->contains($support)) {
            $this->supports->removeElement($support);
            // set the owning side to null (unless already changed)
            if ($support->getPerson() === $this) {
                $support->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<PlacePerson>|PlacePerson[]|null
     */
    public function getPlacePeople(): ?Collection
    {
        return $this->placePeople;
    }

    public function addPlacePerson(PlacePerson $placePerson): self
    {
        if (!$this->placePeople->contains($placePerson)) {
            $this->placePeople[] = $placePerson;
            $placePerson->setPerson($this);
        }

        return $this;
    }

    public function removePlacePerson(PlacePerson $placePerson): self
    {
        if ($this->placePeople->contains($placePerson)) {
            $this->placePeople->removeElement($placePerson);
            // set the owning side to null (unless already changed)
            if ($placePerson->getPerson() === $this) {
                $placePerson->setPerson(null);
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

    public function getSoundexFirstname(): ?string
    {
        return $this->soundexFirstname;
    }

    public function setSoundexFirstname(?string $soundexFirstname): self
    {
        $this->soundexFirstname = $soundexFirstname;

        return $this;
    }

    public function getSoundexLastname(): ?string
    {
        return $this->soundexLastname;
    }

    public function setSoundexLastname(?string $soundexLastname): self
    {
        $this->soundexLastname = $soundexLastname;

        return $this;
    }
}
