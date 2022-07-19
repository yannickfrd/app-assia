<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait ArchivedTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @var \DateTime|null
     */
    protected $archivedAt;

    public function getArchivedAt(): ?\DateTime
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(?\DateTime $archivedAt = null): self
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function isArchived(): bool
    {
        return null !== $this->archivedAt;
    }

    public function getArchivedAtToString(string $format = 'd/m/Y H:i'): string
    {
        return $this->archivedAt ? $this->archivedAt->format($format) : '';
    }
}
