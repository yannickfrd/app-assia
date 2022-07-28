<?php

namespace App\Entity\Admin;

use App\Entity\Traits\GeoLocationEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use App\Repository\Admin\SettingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SettingRepository::class)
 */
class Setting
{
    use TaskSettingTrait;
    use DeletionSettingTrait;
    use LocationEntityTrait;
    use GeoLocationEntityTrait;

    public const DEFAULT_SOFT_DELETION_DELAY = 24;
    public const DEFAULT_HARD_DELETION_DELAY = 12;
    public const DEFAULT_AUTO_ALERT_DELAY = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $organizationName;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=120)
     */
    private $hardDeletionDelay = Setting::DEFAULT_HARD_DELETION_DELAY;

    /*
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=12)
     */
    private $delayToUpdateSiaoRequest = 3;

    /*
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=24)
     */
    private $delayToUpdateSocialHousingRequest = 12;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organizationName;
    }

    public function setOrganizationName(string $organizationName): self
    {
        $this->organizationName = $organizationName;

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

    public function getDelayToUpdateSiaoRequest(): ?int
    {
        return $this->delayToUpdateSiaoRequest;
    }

    public function setDelayToUpdateSiaoRequest(int $delayToUpdateSiaoRequest): self
    {
        $this->delayToUpdateSiaoRequest = $delayToUpdateSiaoRequest;

        return $this;
    }

    public function getDelayToUpdateSocialHousingRequest(): ?int
    {
        return $this->delayToUpdateSocialHousingRequest;
    }

    public function setDelayToUpdateSocialHousingRequest(int $delayToUpdateSocialHousingRequest): self
    {
        $this->delayToUpdateSocialHousingRequest = $delayToUpdateSocialHousingRequest;

        return $this;
    }
}
