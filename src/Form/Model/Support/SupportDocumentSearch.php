<?php

namespace App\Form\Model\Support;

use App\Entity\Support\Document;

class SupportDocumentSearch
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var int|null
     */
    private $type;

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
