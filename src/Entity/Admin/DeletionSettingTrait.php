<?php

namespace App\Entity\Admin;

use Symfony\Component\Validator\Constraints as Assert;

trait DeletionSettingTrait
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=12, max=120)
     */
    private $softDeletionDelay = Setting::DEFAULT_SOFT_DELETION_DELAY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=12, max=120)
     */
    private $hardDeletionDelay = Setting::DEFAULT_SOFT_DELETION_DELAY;

    public function getSoftDeletionDelay(): ?int
    {
        return $this->softDeletionDelay;
    }

    public function setSoftDeletionDelay(?int $softDeletionDelay): self
    {
        $this->softDeletionDelay = $softDeletionDelay;

        return $this;
    }

    public function getHardDeletionDelay(): ?int
    {
        return $this->hardDeletionDelay;
    }

    public function setHardDeletionDelay(?int $hardDeletionDelay): self
    {
        $this->hardDeletionDelay = $hardDeletionDelay;

        return $this;
    }
}
