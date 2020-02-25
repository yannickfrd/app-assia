<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InitEvalGroupRepository")
 */
class InitEvalGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $resourcesGroupAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $debtsGroupAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $housingStatus;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $siaoRequest;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialHousingRequest;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGroup", inversedBy="initEvalGroup", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportGroup;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResourcesGroupAmt(): ?float
    {
        return $this->resourcesGroupAmt;
    }

    public function setResourcesGroupAmt(?float $resourcesGroupAmt): self
    {
        $this->resourcesGroupAmt = $resourcesGroupAmt;

        return $this;
    }

    public function getDebtsGroupAmt(): ?float
    {
        return $this->debtsGroupAmt;
    }

    public function setDebtsGroupAmt(?float $debtsGroupAmt): self
    {
        $this->debtsGroupAmt = $debtsGroupAmt;

        return $this;
    }

    public function getHousingStatus(): ?int
    {
        return $this->housingStatus;
    }

    public function getHousingStatusList()
    {
        return EvalHousingGroup::HOUSING_STATUS[$this->housingStatus];
    }

    public function setHousingStatus(?int $housingStatus): self
    {
        $this->housingStatus = $housingStatus;

        return $this;
    }

    public function getSiaoRequest(): ?int
    {
        return $this->siaoRequest;
    }

    public function setSiaoRequest(?int $siaoRequest): self
    {
        $this->siaoRequest = $siaoRequest;

        return $this;
    }

    public function getSocialHousingRequest(): ?int
    {
        return $this->socialHousingRequest;
    }

    public function setSocialHousingRequest(?int $socialHousingRequest): self
    {
        $this->socialHousingRequest = $socialHousingRequest;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }
}
