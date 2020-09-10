<?php

namespace App\Entity;

use App\Repository\AvdlRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AvdlRepository::class)
 */
class Avdl
{
    public const DIAG_TYPE = [
        1 => 'Léger',
        2 => 'Approfondi',
        99 => 'Non renseigné',
    ];

    public const RECOMMENDATION_SUPPORT = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Injoignable',
        99 => 'Non renseigné',
    ];

    public const SUPPORT_TYPE = [
        1 => 'Prêt au logement (0,25)', // 0.25
        2 => 'Acc. dans le logement (1)', // 1
        3 => 'Propo. en cours (1)', //1
        4 => 'Non prêt au logement (1)', //1
        5 => 'Acc. lourd (2)', //2
    ];
    // 1 => '1 - Léger',
    // 2 => '2 - Moyen',
    // 3 => '3 - Lourd',

    public const END_SUPPORT_REASON = [
        1 => 'Autonome',
        2 => 'Non adhésion',
        3 => 'Transfert (autre département)',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const ACCESS_HOUSING_MODALITY = [
        1 => 'Propo. Bailleur',
        2 => 'Propo. Préfecture',
        3 => 'Propo. DRIHL (Région)',
        5 => 'Propo. Action Logement',
        7 => 'Propo. Mairie',
        6 => 'Protocole Logement d’Abord',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const PROPO_RESULT = [
        1 => 'Favorable',
        2 => 'Refus',
        3 => 'En attente',
        99 => 'Non renseigné',
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
    private $diagType;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $diagStartDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $diagEndDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $recommendationSupport;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $diagComment;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $supportStartDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $supportEndDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $supportType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $supportComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endSupportReason;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $accessHousingModality;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $propoHousingDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $propoResult;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $accessHousingDate;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $endSupportComment;

    /**
     * @ORM\OneToOne(targetEntity=SupportGroup::class, inversedBy="avdl", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiagType(): ?int
    {
        return $this->diagType;
    }

    public function getDiagTypeToString(): ?string
    {
        return $this->getDiagType() ? self::DIAG_TYPE[$this->getDiagType()] : null;
    }

    public function setDiagType(?int $diagType): self
    {
        $this->diagType = $diagType;

        return $this;
    }

    public function getDiagStartDate(): ?\DateTimeInterface
    {
        return $this->diagStartDate;
    }

    public function setDiagStartDate(?\DateTimeInterface $diagStartDate): self
    {
        $this->diagStartDate = $diagStartDate;

        return $this;
    }

    public function getDiagEndDate(): ?\DateTimeInterface
    {
        return $this->diagEndDate;
    }

    public function setDiagEndDate(?\DateTimeInterface $diagEndDate): self
    {
        $this->diagEndDate = $diagEndDate;

        return $this;
    }

    public function getRecommendationSupport(): ?int
    {
        return $this->recommendationSupport;
    }

    public function getRecommendationSupportToString(): ?string
    {
        return $this->getRecommendationSupport() ? self::RECOMMENDATION_SUPPORT[$this->getRecommendationSupport()] : null;
    }

    public function setRecommendationSupport(?int $recommendationSupport): self
    {
        $this->recommendationSupport = $recommendationSupport;

        return $this;
    }

    public function getDiagComment(): ?string
    {
        return $this->diagComment;
    }

    public function setDiagComment(?string $diagComment): self
    {
        $this->diagComment = $diagComment;

        return $this;
    }

    public function getSupportStartDate(): ?\DateTimeInterface
    {
        return $this->supportStartDate;
    }

    public function setSupportStartDate(?\DateTimeInterface $supportStartDate): self
    {
        $this->supportStartDate = $supportStartDate;

        return $this;
    }

    public function getSupportEndDate(): ?\DateTimeInterface
    {
        return $this->supportEndDate;
    }

    public function setSupportEndDate(?\DateTimeInterface $supportEndDate): self
    {
        $this->supportEndDate = $supportEndDate;

        return $this;
    }

    public function getSupportType(): ?int
    {
        return $this->supportType;
    }

    public function getSupportTypeToString(): ?string
    {
        return $this->getSupportType() ? self::SUPPORT_TYPE[$this->getSupportType()] : null;
    }

    public function setSupportType(?int $supportType): self
    {
        $this->supportType = $supportType;

        return $this;
    }

    public function getSupportComment(): ?string
    {
        return $this->supportComment;
    }

    public function setSupportComment(?string $supportComment): self
    {
        $this->supportComment = $supportComment;

        return $this;
    }

    public function getEndSupportReason(): ?int
    {
        return $this->endSupportReason;
    }

    public function getEndSupportReasonToString(): ?string
    {
        return $this->getEndSupportReason() ? self::END_SUPPORT_REASON[$this->getEndSupportReason()] : null;
    }

    public function setEndSupportReason(?int $endSupportReason): self
    {
        $this->endSupportReason = $endSupportReason;

        return $this;
    }

    public function getAccessHousingModality(): ?int
    {
        return $this->accessHousingModality;
    }

    public function getAccessHousingModalityToString(): ?string
    {
        return $this->getAccessHousingModality() ? self::ACCESS_HOUSING_MODALITY[$this->getAccessHousingModality()] : null;
    }

    public function setAccessHousingModality(?int $accessHousingModality): self
    {
        $this->accessHousingModality = $accessHousingModality;

        return $this;
    }

    public function getPropoHousingDate(): ?\DateTimeInterface
    {
        return $this->propoHousingDate;
    }

    public function setPropoHousingDate(?\DateTimeInterface $propoHousingDate): self
    {
        $this->propoHousingDate = $propoHousingDate;

        return $this;
    }

    public function getPropoResult(): ?int
    {
        return $this->propoResult;
    }

    public function getPropoResultToString(): ?string
    {
        return $this->getPropoResult() ? self::PROPO_RESULT[$this->getPropoResult()] : null;
    }

    public function setPropoResult(?int $propoResult): self
    {
        $this->propoResult = $propoResult;

        return $this;
    }

    public function getAccessHousingDate(): ?\DateTimeInterface
    {
        return $this->accessHousingDate;
    }

    public function setAccessHousingDate(?\DateTimeInterface $accessHousingDate): self
    {
        $this->accessHousingDate = $accessHousingDate;

        return $this;
    }

    public function getEndSupportComment(): ?string
    {
        return $this->endSupportComment;
    }

    public function setEndSupportComment(?string $endSupportComment): self
    {
        $this->endSupportComment = $endSupportComment;

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
}
