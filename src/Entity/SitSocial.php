<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SitSocialRepository")
 */
class SitSocial
{
    public const REASON_REQUEST = [
        1 => "Absence de ressource",
        2 => "Départ du département initial",
        3 => "Dort dans la rue",
        4 => "Exil économique",
        5 => "Exil familial",
        6 => "Exil politique",
        7 => "Exil soins",
        8 => "Exil autre motif",
        9 => "Expulsion locative",
        10 => "Fin de prise en charge ASE",
        11 => "Fin d'hébergement chez des tiers",
        12 => "Fin d'hospitalisation",
        13 => "Fin prise en charge Conseil Départemental",
        14 => "Grande exclusion",
        15 => "Inadaptation du logement",
        16 => "Logement insalubre",
        17 => "Logement repris par le propriétaire",
        18 => "Rapprochement du lieu de travail",
        19 => "Regroupement familial",
        20 => "Risque d'expulsion locative",
        21 => "Séparation ou rupture des liens familiaux",
        22 => "Sortie de détention",
        23 => "Sortie de logement accompagné",
        24 => "Sortie d'hébergement",
        25 => "Sortie dispositif asile",
        26 => "Traite humaine",
        27 => "Violences familiales-conjugales",
        98 => "Autre",
        99 => "Non renseigné"
    ];

    public const WANDERING_TIME = [
        1 => "Moins d'une semaine",
        2 => "1 semaine - 1 mois",
        3 => "1 mois - 6 mois",
        4 => "6 mois - 1 an",
        5 => "1 an - 2 ans",
        6 => "2 ans - 5 ans",
        7 => "5 ans - 10 ans",
        8 => "Plus de 10 ans",
        98 => "Autre",
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
    private $reasonRequest;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $wanderingTime;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $speAnimal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $speAnimalName;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $speWheelchair;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $speReducedMobility;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $speViolenceVictim;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $speDomViolenceVictim;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $speASE;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $speOther;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $speOtherPrecision;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $speComment;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGroup", inversedBy="sitSocial", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportGroup;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentSitSocial;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReasonRequest(): ?int
    {
        return $this->reasonRequest;
    }

    public function setReasonRequest(?int $reasonRequest): self
    {
        $this->reasonRequest = $reasonRequest;

        return $this;
    }

    public function getReasonRequestList()
    {
        return self::REASON_REQUEST[$this->reasonRequest];
    }

    public function getWanderingTime(): ?int
    {
        return $this->wanderingTime;
    }

    public function setWanderingTime(?int $wanderingTime): self
    {
        $this->wanderingTime = $wanderingTime;

        return $this;
    }

    public function getWanderingTimeList()
    {
        return self::WANDERING_TIME[$this->wanderingTime];
    }

    public function getSpeAnimal(): ?bool
    {
        return $this->speAnimal;
    }

    public function setSpeAnimal(?bool $speAnimal): self
    {
        $this->speAnimal = $speAnimal;

        return $this;
    }

    public function getSpeAnimalName(): ?string
    {
        return $this->speAnimalName;
    }

    public function setSpeAnimalName(?string $speAnimalName): self
    {
        $this->speAnimalName = $speAnimalName;

        return $this;
    }

    public function getSpeWheelchair(): ?bool
    {
        return $this->speWheelchair;
    }

    public function setSpeWheelchair(?bool $speWheelchair): self
    {
        $this->speWheelchair = $speWheelchair;

        return $this;
    }

    public function getSpeReducedMobility(): ?bool
    {
        return $this->speReducedMobility;
    }

    public function setSpeReducedMobility(?bool $speReducedMobility): self
    {
        $this->speReducedMobility = $speReducedMobility;

        return $this;
    }

    public function getSpeViolenceVictim(): ?bool
    {
        return $this->speViolenceVictim;
    }

    public function setSpeViolenceVictim(?bool $speViolenceVictim): self
    {
        $this->speViolenceVictim = $speViolenceVictim;

        return $this;
    }

    public function getSpeDomViolenceVictim(): ?bool
    {
        return $this->speDomViolenceVictim;
    }

    public function setSpeDomViolenceVictim(?bool $speDomViolenceVictim): self
    {
        $this->speDomViolenceVictim = $speDomViolenceVictim;

        return $this;
    }

    public function getSpeASE(): ?bool
    {
        return $this->speASE;
    }

    public function setSpeASE(?bool $speASE): self
    {
        $this->speASE = $speASE;

        return $this;
    }

    public function getSpeOther(): ?bool
    {
        return $this->speOther;
    }

    public function setSpeOther(?bool $speOther): self
    {
        $this->speOther = $speOther;

        return $this;
    }

    public function getSpeOtherPrecision(): ?string
    {
        return $this->speOtherPrecision;
    }

    public function setSpeOtherPrecision(?string $speOtherPrecision): self
    {
        $this->speOtherPrecision = $speOtherPrecision;

        return $this;
    }

    public function getSpeComment(): ?string
    {
        return $this->speComment;
    }

    public function setSpeComment(?string $speComment): self
    {
        $this->speComment = $speComment;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }

    public function getCommentSitSocial(): ?string
    {
        return $this->commentSitSocial;
    }

    public function setCommentSitSocial(?string $commentSitSocial): self
    {
        $this->commentSitSocial = $commentSitSocial;

        return $this;
    }
}
