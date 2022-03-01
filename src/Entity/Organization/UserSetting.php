<?php

namespace App\Entity\Organization;

use App\Entity\Admin\TaskSettingTrait;
use App\Repository\Admin\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SettingRepository::class)
 */
class UserSetting
{
    use TaskSettingTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
