<?php

namespace App\Entity\Admin;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait DeletionSettingTrait
{
    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=12, max=120)
     */
    private $softDeletionDelay = Setting::DEFAULT_SOFT_DELETION_DELAY;

    public function getSoftDeletionDelay(): ?int
    {
        return $this->softDeletionDelay;
    }

    public function setSoftDeletionDelay(?int $softDeletionDelay): self
    {
        $this->softDeletionDelay = $softDeletionDelay;

        return $this;
    }
}
