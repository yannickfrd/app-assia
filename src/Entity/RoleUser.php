<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleUserRepository")
 */
class RoleUser
{
    public const ROLE = [
        1 => "Travailleur/euse social·e",
        2 => "Chef·fe de service",
        3 => "Directeur/trice",
        4 => "Administratif",
        5 => "Chargé de mission",
        6 => "Stagiaire",
        7 => "Autre",
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
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="roleUsers")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Department", inversedBy="roleUsers")
     */
    private $department;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): self
    {
        $this->department = $department;

        return $this;
    }
}
