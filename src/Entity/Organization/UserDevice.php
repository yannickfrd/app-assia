<?php

namespace App\Entity\Organization;

use App\Repository\Organization\UserDeviceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserDeviceRepository::class)
 */
class UserDevice
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $nbSupports;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="userDevices", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Device::class, inversedBy="userDevices", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $device;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbSupports(): ?float
    {
        return $this->nbSupports;
    }

    public function setNbSupports(?float $nbSupports): self
    {
        $this->nbSupports = $nbSupports;

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

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): self
    {
        $this->device = $device;

        return $this;
    }
}
