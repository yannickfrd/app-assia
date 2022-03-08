<?php

namespace App\Form\Model\Support;

use App\Entity\Support\Document;
use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Organization\ReferentServiceDeviceSearchTrait;
use Doctrine\Common\Collections\ArrayCollection;

class DocumentSearch
{
    use ReferentServiceDeviceSearchTrait;
    use DateSearchTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $name;

    /** @var ArrayCollection|null */
    protected $tags;

    public function getTags(): ?ArrayCollection
    {
        return $this->tags;
    }

    public function setTags(?ArrayCollection $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
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
}
