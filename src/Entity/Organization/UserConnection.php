<?php

namespace App\Entity\Organization;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Organization\UserConnectionRepository")
 */
class UserConnection
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $connectionAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="userConnections")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConnectionAt(): ?\DateTimeInterface
    {
        return $this->connectionAt;
    }

    public function setConnectionAt(\DateTimeInterface $connectionAt): self
    {
        $this->connectionAt = $connectionAt;

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
}
