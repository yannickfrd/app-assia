<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeviceRepository")
 * @UniqueEntity(
 *  fields={"name"},
 *  message="Ce nom de dispositif existe déjà."
 * )
 */
class Device
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float", nullable=true, options={"default":1})
     * @Assert\Range(min = 0, max = 10, minMessage="Le coefficient ne peut être inférieur à 0",  maxMessage="Le coefficient ne peut être supérieur à 10")
     */
    private $coefficient = 1;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notesCreated")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notesUpdated")
     */
    private $updatedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ServiceDevice", mappedBy="device", orphanRemoval=true, cascade={"persist"})     
     */
    private $serviceDevices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Accommodation", mappedBy="device")
     */
    private $accommodations;


    public function __construct()
    {
        $this->serviceDevices = new ArrayCollection();
        $this->accommodations = new ArrayCollection();
    }


    public function __toString()
    {
        return strval($this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCoefficient(): ?float
    {
        return $this->coefficient;
    }

    public function setCoefficient(?float $coefficient): self
    {
        $this->coefficient = $coefficient;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return Collection|ServiceDevice[]
     */
    public function getServiceDevices(): Collection
    {
        return $this->serviceDevices;
    }

    public function addServiceDevice(ServiceDevice $serviceDevice): self
    {
        if (!$this->serviceDevices->contains($serviceDevice)) {
            $this->serviceDevices[] = $serviceDevice;
            $serviceDevice->setDevice($this);
        }

        return $this;
    }

    public function removeServiceDevice(ServiceDevice $serviceDevice): self
    {
        if ($this->serviceDevices->contains($serviceDevice)) {
            $this->serviceDevices->removeElement($serviceDevice);
            // set the owning side to null (unless already changed)
            if ($serviceDevice->getDevice() === $this) {
                $serviceDevice->setDevice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Accommodation[]
     */
    public function getAccommodations(): Collection
    {
        return $this->accommodations;
    }

    public function addAccommodation(Accommodation $accommodation): self
    {
        if (!$this->accommodations->contains($accommodation)) {
            $this->accommodations[] = $accommodation;
            $accommodation->setDevice($this);
        }

        return $this;
    }

    public function removeAccommodation(Accommodation $accommodation): self
    {
        if ($this->accommodations->contains($accommodation)) {
            $this->accommodations->removeElement($accommodation);
            // set the owning side to null (unless already changed)
            if ($accommodation->getDevice() === $this) {
                $accommodation->setDevice(null);
            }
        }

        return $this;
    }
}
