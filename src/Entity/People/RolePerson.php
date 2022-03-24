<?php

namespace App\Entity\People;

use App\Form\Utils\Choices;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\People\RolePersonRepository")
 * @UniqueEntity(
 *     fields={"person", "peopleGroup"},
 *     errorPath="person",
 *     message="Cette personne est déjà dans le groupe.")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class RolePerson
{
    use SoftDeleteableEntity;

    public const ROLE_CHILD = 3;

    public const ROLE = [
        1 => 'Conjoint·e',
        2 => 'Époux/se',
        3 => 'Enfant',
        4 => 'Parent isolé',
        5 => 'Personne isolée',
        6 => 'Autre membre de la famille',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", options={"default":0})
     */
    private $head;

    /**
     * @Groups("export").
     */
    private $headToString;

    /**
     * @ORM\Column(type="smallint")
     * @Assert\NotNull(message="Le rôle ne doit pas être vide.")
     */
    private $role;

    /**
     * @Groups("export").
     */
    private $roleToString;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\People\Person", inversedBy="rolesPerson", cascade={"persist"})
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     * @Assert\Valid()
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\People\PeopleGroup", inversedBy="rolePeople", cascade={"persist"})
     * @Assert\Valid()
     */
    private $peopleGroup;

    /**
     * @var \DateTimeInterface
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $createdAt; // NE PAS SUPPRIMER

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHead(): ?bool
    {
        return $this->head;
    }

    public function getHeadToString(): ?string
    {
        return Choices::YES_NO_BOOLEAN[$this->head];
    }

    public function setHead(?bool $head): self
    {
        $this->head = $head;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function getRoleToString(): ?string
    {
        return self::ROLE[$this->role];
    }

    public function setRole(?int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getPeopleGroup(): ?PeopleGroup
    {
        return $this->peopleGroup;
    }

    public function setPeopleGroup(?PeopleGroup $peopleGroup): self
    {
        $this->peopleGroup = $peopleGroup;

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
}
