<?php

namespace App\Entity;

use App\Entity\Person;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass="App\Repository\RolePersonRepository")
 * @UniqueEntity(
 *     fields={"person", "groupPeople"},
 *     errorPath="person",
 *     message="Cette personne est déjà dans le groupe.")
 */
class RolePerson
{
    public const ROLE = [
        1 => "Conjoint·e",
        2 => "Époux/se",
        3 => "Enfant",
        4 => "Parent isolé",
        5 => "Personne isolée",
        6 => "Autre membre de la famille",
        7 => "Autre"
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
     * @ORM\Column(type="smallint")
     * @Assert\NotNull(message="Le rôle ne doit pas être vide.")
     * @Assert\Range(min = 1, max = 7, minMessage="Ne doit pas être vide.",  maxMessage="Le rôle ne doit pas être vide.")
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="rolesPerson", cascade={"persist"})
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)   
     * @Assert\Valid
     */
    private $person;

    //  * @Assert\All(constraints={
    //  *      @Assert\NotBlank(),
    //  *      @Assert\NotNull,
    //  *      @Assert\Length(min=2, max=50),
    //  * })

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupPeople", inversedBy="rolePerson", cascade={"persist"})
     * @ORM\JoinColumn(name="group_people_id", referencedColumnName="id")
     * @Assert\Valid
     */
    private $groupPeople;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $createdBy;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHead(): ?bool
    {
        return $this->head;
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

    public function setRole(?int $role): self
    {
        $this->role = $role;

        return $this;
    }


    public function getRoleList()
    {
        return self::ROLE[$this->role];
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

    public function getGroupPeople(): ?GroupPeople
    {
        return $this->groupPeople;
    }

    public function setGroupPeople(?GroupPeople $groupPeople): self
    {
        $this->groupPeople = $groupPeople;

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
}
