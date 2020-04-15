<?php

namespace App\Entity\Traits;

trait DisableEntityTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $disabledAt;

    public function setDisabledAt(?\DateTime $disabledAt = null): self
    {
        $this->disabledAt = $disabledAt;

        return $this;
    }

    public function getDisabledAt(): ?\DateTime
    {
        return $this->disabledAt;
    }

    public function isDisabled(): ?bool
    {
        return null !== $this->disabledAt;
    }
}
