<?php

namespace App\Entity\Organization;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Repository\Organization\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TagRepository::class)
 */
class Tag
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    public const COLORS = [
        'primary' => 'Bleu',
        'secondary' => 'Gris',
        'success' => 'Vert',
        'warning' => 'Jaune',
        'orange2' => 'Orange',
        'danger' => 'Rouge',
        'brown' => 'Marron',
    ];

    public const DEFAULT_COLOR = 'secondary';

    public const CATEGORIES = [
        'document' => 'Document',
        'note' => 'Note',
        'rdv' => 'Rendez-vous',
        'support' => 'Suivi',
        'event' => 'Tâche',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("show_tag")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     * @Groups("show_tag")
     */
    private $name;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("show_tag")
     */
    private $color;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $categories;

    /**
     * @ORM\ManyToMany(targetEntity=Service::class, mappedBy="tags")
     */
    private $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->id;
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
        $this->name = ucfirst($name);

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color ?? self::DEFAULT_COLOR;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getColorToString(): ?string
    {
        return $this->color ? self::COLORS[$this->color] : null;
    }

    public function getCategories(): ?array
    {
        return explode('|', $this->categories);
    }

    public function getCategoriesToString(): ?string
    {
        if (!$this->categories) {
            return null;
        }

        $categories = [];

        foreach (explode('|', $this->categories) as $categorie) {
            $categories[] = self::CATEGORIES[$categorie];
        }

        return join(', ', $categories);
    }

    public function setCategories($categories): self
    {
        if ($categories) {
            $this->categories = join('|', $categories);
        }

        return $this;
    }

    /**
     * @return Collection<Service>|null
     */
    public function getServices(): ?Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services[] = $service;

            $service->addTag($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        $this->services->removeElement($service);

        return $this;
    }
}
