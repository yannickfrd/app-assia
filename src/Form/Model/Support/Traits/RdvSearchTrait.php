<?php

namespace App\Form\Model\Support\Traits;

trait RdvSearchTrait
{
    /**
     * @var string|null
     */
    private $title;

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
