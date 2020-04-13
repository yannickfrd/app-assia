<?php

namespace App\Entity;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrganizationRepository")
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
     */
    private $name;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\OriginRequest", mappedBy="organization")
     */
    private $originRequests;

    // /**
    //  * @ORM\ManyToMany(targetEntity="App\Entity\Service", inversedBy="organizations")
    //  */
    // private $service;

    public function __construct()
    {
        $this->originRequests = new ArrayCollection();
        // $this->service = new ArrayCollection();
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
     * @return Collection|OriginRequest[]
     */
    public function getOriginRequests(): ?Collection
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

    // /**
    //  * @return Collection|Service[]
    //  */
    // public function getService(): ?Collection
    // {
    //     return $this->service;
    // }

    // public function addService(Service $service): self
    // {
    //     if (!$this->service->contains($service)) {
    //         $this->service[] = $service;
    //     }

    //     return $this;
    // }

    // public function removeService(Service $service): self
    // {
    //     if ($this->service->contains($service)) {
    //         $this->service->removeElement($service);
    //     }

    //     return $this;
    // }
}
