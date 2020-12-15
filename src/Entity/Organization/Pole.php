<?php

namespace App\Entity\Organization;

use App\Entity\Traits\ContactEntityTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DisableEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Organization\PoleRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Ce pôle existe déjà !")
 */
class Pole
{
    use ContactEntityTrait;
    use LocationEntityTrait;
    use CreatedUpdatedEntityTrait;
    use DisableEntityTrait;

    public const POLES = [
        1 => 'Accueil Publics Migrants',
        2 => 'Insertion-Formation',
        3 => 'Habitat et Accès au Logement',
        4 => 'Hébergement Social',
        5 => 'SIAO',
        6 => 'Socio-judiciaire',
    ];

    public const COLOR = [
        'beige' => 'Beige',
        'pink2' => 'Rouge rose',
        'primary' => 'Bleu',
        'cyan2' => 'Cyan',
        'brown' => 'Marron',
        'dark' => 'Noir',
        'orange2' => 'Orange',
        'green2' => 'Vert',
        'purple' => 'Violet',
    ];

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
     * @ORM\OneToMany(targetEntity="App\Entity\Organization\Service", mappedBy="pole")
     */
    private $services;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoPath;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User")
     */
    private $chief;

    public function construct()
    {
        $this->services = new ArrayCollection();
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoPath(?string $logoPath): self
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    /**
     * @return Service[]|null
     */
    public function getServices()
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;
            $service->setPole($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->contains($service)) {
            $this->services->removeElement($service);
            // set the owning side to null (unless already changed)
            if ($service->getPole() === $this) {
                $service->setPole(null);
            }
        }

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
}
