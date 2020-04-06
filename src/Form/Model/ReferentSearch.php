<?php

namespace App\Form\Model;

class ReferentSearch
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
}
