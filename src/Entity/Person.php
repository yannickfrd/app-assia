<?php

namespace App\Entity;

use App\Entity\Traits\ContactEntityTrait;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
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

    public const GENDER = [
        1 => 'Femme',
        2 => 'Homme',
        3 => 'Autre',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
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
     * Groups("export")
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
     * Groups("export")
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
     * Groups("export")
     */
    private $birthdate;

    /**
     * @Assert\Range(
     * min = 0,
     * max = 90,
     * minMessage = "La date de naissance est incorrect.",
     * maxMessage = "La date de naissance est incorrect.")
     * Groups("export")
     */
    private $age;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     * @Assert\NotNull(message="Le sexe doit être renseigné.")
     * @Assert\Range(min = 1, max = 3, minMessage="Le sexe doit être renseigné.",  maxMessage="Le sexe doit être renseigné.")
     */
    private $gender;

    /**
     * Groups("export").
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
     * @ORM\OneToMany(targetEntity="App\Entity\RolePerson", mappedBy="person", orphanRemoval=true, cascade={"persist"})
     * @Assert\Valid()
     */
    private $rolesPerson;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportPerson", mappedBy="person", orphanRemoval=true, cascade={"persist"})
     */
    private $supports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccommodationPerson", mappedBy="person", orphanRemoval=true)
     */
    private $accommodationPeople;

    public function __construct()
    {
        $this->rolesPerson = new ArrayCollection();
        $this->supports = new ArrayCollection();
        $this->accommodationPeople = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->lastname.' '.$this->firstname;
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
        $this->firstname = ucfirst($firstname);

        return $this;
    }

    public function getSlug(): string
    {
        $slugify = new Slugify();

        return $slugify->slugify($this->firstname.'-'.$this->lastname);
    }

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

    public function setUsename(string $usename): self
    {
        $this->usename = $usename;

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
            return $this->birthdate->diff(new \DateTime())->y;
        } else {
            return null;
        }
    }

    public function setAge(?\DateTimeInterface $birthdate): self
    {
        if ($birthdate) {
            $now = new \DateTime();
            $this->age = $birthdate->diff($now)->y;
        }

        return $this;
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
        return self::GENDER[$this->gender];
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
     * @return Collection|RolePerson[]
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
     * @return Collection|SupportPerson[]
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
     * @return Collection|AccommodationPerson[]
     */
    public function getAccommodationPeople(): ?Collection
    {
        return $this->accommodationPeople;
    }

    public function addAccommodationPerson(AccommodationPerson $accommodationPerson): self
    {
        if (!$this->accommodationPeople->contains($accommodationPerson)) {
            $this->accommodationPeople[] = $accommodationPerson;
            $accommodationPerson->setPerson($this);
        }

        return $this;
    }

    public function removeAccommodationPerson(AccommodationPerson $accommodationPerson): self
    {
        if ($this->accommodationPeople->contains($accommodationPerson)) {
            $this->accommodationPeople->removeElement($accommodationPerson);
            // set the owning side to null (unless already changed)
            if ($accommodationPerson->getPerson() === $this) {
                $accommodationPerson->setPerson(null);
            }
        }

        return $this;
    }
}
