<?php

namespace App\Form\Model\Traits;

trait ContributionSearchTrait
{
    /**
     * @var int|null
     */
    private $type;

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
