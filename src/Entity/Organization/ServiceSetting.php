<?php

namespace App\Entity\Organization;

use App\Entity\Admin\DeletionSettingTrait;
use App\Entity\Admin\TaskSettingTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SettingRepository::class)
 */
class ServiceSetting
{
    use TaskSettingTrait;
    use DeletionSettingTrait;

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
