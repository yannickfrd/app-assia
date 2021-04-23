<?php

namespace App\Entity\Organization;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Support\OriginRequest;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Organization\OrganizationRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Cet organisme existe déjà !")
 */
class Organization
{
    use CreatedUpdatedEntityTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("export")
     */
    private $name;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("export")
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\OriginRequest", mappedBy="organization")
     */
    private $originRequests;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, mappedBy="organizations")
     */
    private $services;

    public function __construct()
    {
        $this->originRequests = new ArrayCollection();
        $this->services = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    public function getOrganization(): ?Organization
    {
        return $this;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

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
     * @return Collection<OriginRequest>
     */
    public function getOriginRequests()
    {
        return $this->originRequests;
    }

    public function addOriginRequest(OriginRequest $originRequest): self
    {
        if (!$this->originRequests->contains($originRequest)) {
            $this->originRequests[] = $originRequest;
            $originRequest->setOrganization($this);
        }

        return $this;
    }

    public function removeOriginRequest(OriginRequest $originRequest): self
    {
        if ($this->originRequests->contains($originRequest)) {
            $this->originRequests->removeElement($originRequest);
            // set the owning side to null (unless already changed)
            if ($originRequest->getOrganization() === $this) {
                $originRequest->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<Service>
     */
    public function getServices()
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->addOrganization($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->contains($service)) {
            $this->services->removeElement($service);
            $service->removeOrganization($this);
        }

        return $this;
    }
}
