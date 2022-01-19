<?php

namespace App\Entity\Organization;

use App\Entity\Admin\Setting;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ConfigRepository::class)
 */
class ServiceSetting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $softDeletionDelay = Setting::DEFAULT_SOFT_DELETION_DELAY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $hardDeletionDelay = Setting::DEFAULT_HARD_DELETION_DELAY;

    /**
     * @ORM\Column(type="boolean")
     */
    private $weeklyAlert = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $dailyAlert = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSoftDeletionDelay(): ?int
    {
        return $this->softDeletionDelay;
    }

    public function setSoftDeletionDelay(?int $softDeletionDelay): self
    {
        $this->softDeletionDelay = $softDeletionDelay;

        return $this;
    }

    public function getHardDeletionDelay(): int
    {
        return $this->hardDeletionDelay;
    }

    public function setHardDeletionDelay(?int $hardDeletionDelay): self
    {
        $this->hardDeletionDelay = $hardDeletionDelay;

        return $this;
    }

    public function getWeeklyAlert(): bool
    {
        return $this->weeklyAlert;
    }

    public function setWeeklyAlert(bool $weeklyAlert): self
    {
        $this->weeklyAlert = $weeklyAlert;

        return $this;
    }

    public function getDailyAlert(): bool
    {
        return $this->dailyAlert;
    }

    public function setDailyAlert(bool $dailyAlert): self
    {
        $this->dailyAlert = $dailyAlert;

        return $this;
    }
}
