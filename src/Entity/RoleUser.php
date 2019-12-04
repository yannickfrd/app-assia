<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoleUserRepository")
 */
class RoleUser
{
    public const ROLE = [
        1 => "Travailleur social",
        2 => "Coordinatrice/teur",
        3 => "Chef·fe de service",
        4 => "Directrice/teur",
        5 => "Administratif",
        6 => "Chargé·e de mission",
        7 => "Stagiaire",
        98 => "Autre",
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
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="roleUser", cascade={"persist"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="roleUser", cascade={"persist"})
     */
    private $service;


    // public function __toString()
    // {
    //     return $this->id;
    // }

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

    public function getRoleType()
    {
        return self::ROLE[$this->role];
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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }
}
