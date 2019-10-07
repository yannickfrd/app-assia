<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RolePersonRepository")
 */
class RolePerson
{
    public const ROLE = [
        NULL => "",
        1 => "Demandeur",
        2 => "Conjoint·e",
        3 => "Époux/se",
        4 => "Enfant",
        5 => "Membre de la famille",
        6 => "Parent isolé",
        7 => "Personne isolée",
        8 => "Autre"
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
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="rolesPerson", cascade={"persist"})
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", nullable=false)
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupPeople", inversedBy="rolePerson", cascade={"persist"})
     * @ORM\JoinColumn(name="group_people_id", referencedColumnName="id")
     */
    private $groupPeople;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHead(): ?bool
    {
        return $this->head;
    }

    public function setHead(bool $head): self
    {
        $this->head = $head;

        return $this;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function setRole(int $role): self
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

    public function getGroupPeople(): ?GroupPeople
    {
        return $this->groupPeople;
    }

    public function setGroupPeople(?GroupPeople $groupPeople): self
    {
        $this->groupPeople = $groupPeople;

        return $this;
    }

    public function listRole() 
    {
        return self::ROLE[$this->role];
    }
}
