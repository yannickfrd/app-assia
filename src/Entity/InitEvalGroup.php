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
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $resourcesGroup;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debtsGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGroup", inversedBy="initEvalGroup", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportGroup;


    public function getId(): ?int
    {
        return $this->id;
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

    public function getResourcesGroup(): ?int
    {
        return $this->resourcesGroup;
    }

    public function setResourcesGroup(?int $resourcesGroup): self
    {
        $this->resourcesGroup = $resourcesGroup;

        return $this;
    }

    public function getDebtsGroup(): ?int
    {
        return $this->debtsGroup;
    }

    public function setDebtsGroup(?int $debtsGroup): self
    {
        $this->debtsGroup = $debtsGroup;

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
