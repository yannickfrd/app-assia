<?php

namespace App\Entity;

use App\Entity\Traits\ContactEntityTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DisableEntityTrait;
use App\Repository\SubServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SubServiceRepository::class)
 */
class SubService
{
    use ContactEntityTrait;
    use CreatedUpdatedEntityTrait;
    use DisableEntityTrait;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du service ne doit pas être vide.")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $chief;

    /**
     * @ORM\OneToMany(targetEntity=SupportGroup::class, mappedBy="subService")
     */
    private $supportGroup;

    /**
     * @ORM\ManyToOne(targetEntity=Service::class, inversedBy="subServices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity=Accommodation::class, mappedBy="subService")
     */
    private $accommodations;

    public function __construct()
    {
        $this->supportGroup = new ArrayCollection();
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

    public function getChief(): ?User
    {
        return $this->chief;
    }

    public function setChief(?User $chief): self
    {
        $this->chief = $chief;

        return $this;
    }

    /**
     * @return Collection|SupportGroup[]
     */
    public function getSupportGroup(): Collection
    {
        return $this->supportGroup;
    }

    public function addSupportGroup(SupportGroup $supportGroup): self
    {
        if (!$this->supportGroup->contains($supportGroup)) {
            $this->supportGroup[] = $supportGroup;
            $supportGroup->setSubService($this);
        }

        return $this;
    }

    public function removeSupportGroup(SupportGroup $supportGroup): self
    {
        if ($this->supportGroup->contains($supportGroup)) {
            $this->supportGroup->removeElement($supportGroup);
            // set the owning side to null (unless already changed)
            if ($supportGroup->getSubService() === $this) {
                $supportGroup->setSubService(null);
            }
        }

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
            $accommodation->setSubService($this);
        }

        return $this;
    }

    public function removeAccommodation(Accommodation $accommodation): self
    {
        if ($this->accommodations->contains($accommodation)) {
            $this->accommodations->removeElement($accommodation);
            // set the owning side to null (unless already changed)
            if ($accommodation->getSubService() === $this) {
                $accommodation->setSubService(null);
            }
        }

        return $this;
    }
}
