<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SitFamilyGrpRepository")
 */
class SitFamilyGrp
{
    public const PREGNANCY_TYPE = [
        1 => "Simple",
        2 => "Jumeaux",
        3 => "Multiple",
        99 => "Non renseigné"
    ];

    public const FAML_REUNIFICATION = [
        1 => "Oui",
        2 => "Non",
        3 => "Envisagé",
        4 => "En cours",
        5 => "Accepté",
        99 => "Non renseigné"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $nbDependentChildren;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $unbornChild;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $expDateChildbirth;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pregnancyType;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $famlReunification;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $nbPeopleReunification;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cafId;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentSitFamilyGrp;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGrp", inversedBy="sitFamilyGrp")
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportGrp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNbDependentChildren(): ?int
    {
        return $this->nbDependentChildren;
    }

    public function setNbDependentChildren(?int $nbDependentChildren): self
    {
        $this->nbDependentChildren = $nbDependentChildren;

        return $this;
    }

    public function getUnbornChild(): ?int
    {
        return $this->unbornChild;
    }

    public function setUnbornChild(?int $unbornChild): self
    {
        $this->unbornChild = $unbornChild;

        return $this;
    }

    public function getExpDateChildbirth(): ?\DateTimeInterface
    {
        return $this->expDateChildbirth;
    }

    public function setExpDateChildbirth(?\DateTimeInterface $expDateChildbirth): self
    {
        $this->expDateChildbirth = $expDateChildbirth;

        return $this;
    }

    public function getPregnancyType(): ?int
    {
        return $this->pregnancyType;
    }

    public function setPregnancyType(?int $pregnancyType): self
    {
        $this->pregnancyType = $pregnancyType;

        return $this;
    }

    public function getPregnancyTypeList()
    {
        return self::PREGNANCY_TYPE[$this->pregnancyType];
    }

    public function getFamlReunification(): ?int
    {
        return $this->famlReunification;
    }

    public function setFamlReunification(?int $famlReunification): self
    {
        $this->famlReunification = $famlReunification;

        return $this;
    }

    public function getFamlReunificationList()
    {
        return self::FAML_REUNIFICATION[$this->famlReunification];
    }

    public function getNbPeopleReunification(): ?int
    {
        return $this->nbPeopleReunification;
    }

    public function setNbPeopleReunification(?int $nbPeopleReunification): self
    {
        $this->nbPeopleReunification = $nbPeopleReunification;

        return $this;
    }

    public function getCafId(): ?string
    {
        return $this->cafId;
    }

    public function setCafId(?string $cafId): self
    {
        $this->cafId = $cafId;

        return $this;
    }

    public function getCommentSitFamilyGrp(): ?string
    {
        return $this->commentSitFamilyGrp;
    }

    public function setCommentSitFamilyGrp(?string $commentSitFamilyGrp): self
    {
        $this->commentSitFamilyGrp = $commentSitFamilyGrp;

        return $this;
    }

    public function getSupportGrp(): ?SupportGrp
    {
        return $this->supportGrp;
    }

    public function setSupportGrp(SupportGrp $supportGrp): self
    {
        $this->supportGrp = $supportGrp;

        return $this;
    }
}
