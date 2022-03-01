<?php

namespace App\Entity\Admin;

use Symfony\Component\Validator\Constraints as Assert;

trait TaskSettingTrait
{
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $weeklyAlert = true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $dailyAlert = true;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $autoEvaluationAlerts = true;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=12)
     */
    protected $endValidPermitDateDelay = Setting::DEFAULT_AUTO_ALERT_DELAY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=12)
     */
    protected $endRightsSocialSecurityDateDelay = Setting::DEFAULT_AUTO_ALERT_DELAY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=12)
     */
    protected $endRqthDateDelay = Setting::DEFAULT_AUTO_ALERT_DELAY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=12)
     */
    protected $endRightsDateDelay = Setting::DEFAULT_AUTO_ALERT_DELAY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=12)
     */
    protected $siaoUpdatedRequestDateDelay = 0;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=12)
     */
    protected $socialHousingUpdatedRequestDateDelay = Setting::DEFAULT_AUTO_ALERT_DELAY;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Range(min=0, max=12)
     */
    protected $endDomiciliationDateDelay = Setting::DEFAULT_AUTO_ALERT_DELAY;

    public function getWeeklyAlert(): ?bool
    {
        return $this->weeklyAlert;
    }

    public function setWeeklyAlert(?bool $weeklyAlert): self
    {
        $this->weeklyAlert = $weeklyAlert;

        return $this;
    }

    public function getDailyAlert(): ?bool
    {
        return $this->dailyAlert;
    }

    public function setDailyAlert(?bool $dailyAlert): self
    {
        $this->dailyAlert = $dailyAlert;

        return $this;
    }

    public function getAutoEvaluationAlerts(): ?bool
    {
        return $this->autoEvaluationAlerts;
    }

    public function setAutoEvaluationAlerts(?bool $autoEvaluationAlerts): self
    {
        $this->autoEvaluationAlerts = $autoEvaluationAlerts;

        return $this;
    }

    public function getEndValidPermitDateDelay(): ?int
    {
        return $this->endValidPermitDateDelay;
    }

    public function setEndValidPermitDateDelay(?int $endValidPermitDateDelay): self
    {
        $this->endValidPermitDateDelay = $endValidPermitDateDelay;

        return $this;
    }

    public function getEndRightsSocialSecurityDateDelay(): ?int
    {
        return $this->endRightsSocialSecurityDateDelay;
    }

    public function setEndRightsSocialSecurityDateDelay(?int $endRightsSocialSecurityDateDelay): self
    {
        $this->endRightsSocialSecurityDateDelay = $endRightsSocialSecurityDateDelay;

        return $this;
    }

    public function getEndRqthDateDelay(): ?int
    {
        return $this->endRqthDateDelay;
    }

    public function setEndRqthDateDelay(?int $endRqthDateDelay): self
    {
        $this->endRqthDateDelay = $endRqthDateDelay;

        return $this;
    }

    public function getEndRightsDateDelay(): ?int
    {
        return $this->endRightsDateDelay;
    }

    public function setEndRightsDateDelay(?int $endRightsDateDelay): self
    {
        $this->endRightsDateDelay = $endRightsDateDelay;

        return $this;
    }

    public function getSiaoUpdatedRequestDateDelay(): ?int
    {
        return $this->siaoUpdatedRequestDateDelay;
    }

    public function setSiaoUpdatedRequestDateDelay(?int $siaoUpdatedRequestDateDelay): self
    {
        $this->siaoUpdatedRequestDateDelay = $siaoUpdatedRequestDateDelay;

        return $this;
    }

    public function getSocialHousingUpdatedRequestDateDelay(): ?int
    {
        return $this->socialHousingUpdatedRequestDateDelay;
    }

    public function setSocialHousingUpdatedRequestDateDelay(?int $socialHousingUpdatedRequestDateDelay): self
    {
        $this->socialHousingUpdatedRequestDateDelay = $socialHousingUpdatedRequestDateDelay;

        return $this;
    }

    public function getEndDomiciliationDateDelay(): ?int
    {
        return $this->endDomiciliationDateDelay;
    }

    public function setEndDomiciliationDateDelay(?int $endDomiciliationDateDelay): self
    {
        $this->endDomiciliationDateDelay = $endDomiciliationDateDelay;

        return $this;
    }
}
