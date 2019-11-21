<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SitFamilyPersRepository")
 */
class SitFamilyPers
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
    private $maritalStatus;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childcareSchool;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $childcareSchoolLocation;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childToHost;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childDependance;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Supportpers", inversedBy="sitFamilyPers", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportPers;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaritalStatus(): ?int
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(?int $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getChildcareSchool(): ?int
    {
        return $this->childcareSchool;
    }

    public function setChildcareSchool(?int $childcareSchool): self
    {
        $this->childcareSchool = $childcareSchool;

        return $this;
    }

    public function getChildcareSchoolLocation(): ?string
    {
        return $this->childcareSchoolLocation;
    }

    public function setChildcareSchoolLocation(?string $childcareSchoolLocation): self
    {
        $this->childcareSchoolLocation = $childcareSchoolLocation;

        return $this;
    }

    public function getChildToHost(): ?int
    {
        return $this->childToHost;
    }

    public function setChildToHost(?int $childToHost): self
    {
        $this->childToHost = $childToHost;

        return $this;
    }

    public function getChildDependance(): ?int
    {
        return $this->childDependance;
    }

    public function setChildDependance(?int $childDependance): self
    {
        $this->childDependance = $childDependance;

        return $this;
    }

    public function getSupportPers(): ?Supportpers
    {
        return $this->supportPers;
    }

    public function setSupportPers(Supportpers $supportPers): self
    {
        $this->supportPers = $supportPers;

        return $this;
    }
}
