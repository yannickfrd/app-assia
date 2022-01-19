<?php

namespace App\Entity\Organization;

use App\Repository\Admin\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SettingRepository::class)
 */
class UserSetting
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

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
