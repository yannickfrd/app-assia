<?php

namespace App\Entity\Traits;

trait DeletedTrait
{
    /** @var bool */
    private $deleted = false;

    public function getDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }
}
