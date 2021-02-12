<?php

namespace App\Form\Model\Support;

use App\Entity\Support\Document;
use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Organization\ReferentServiceDeviceSearchTrait;

class DocumentSearch
{
    use ReferentServiceDeviceSearchTrait;
    use DateSearchTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $name;

    /** @var int|null */
    private $type;

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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeString()
    {
        return Document::TYPE[$this->type];
    }
}
