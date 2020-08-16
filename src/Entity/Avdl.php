<?php

namespace App\Entity;

use App\Form\Utils\Choices;
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
        1 => '1 - Léger',
        2 => '2 - Moyen',
        3 => '3 - Lourd',
    ];

    public const END_SUPPORT_REASON = [
        1 => 'Autonome',
        2 => 'Non adhésion',
        3 => 'Transfert (autre département)',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const ACCESS_HOUSING_MODALITY = [
        1 => 'Propo. bailleur',
        2 => 'Propo. Préfecture',
        3 => 'Propo. DRIHL',
        5 => 'Propo. Action Logement',
        6 => 'Protocole Logement d’Abord',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const PROPO_ORIGIN = [
        1 => 'Préfecture',
        2 => 'Action Logement',
        3 => 'DRIHL (Région)',
        5 => 'Mairie',
        6 => 'Protocoles',
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
     * @ORM\Column(type="date", nullable=true)
     */
    private $mandateDate; // A supprimer ?

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cityOrigin; // A supprimer ?

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $propoHousing;

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
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $readyToHousing;

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
    private $propoOrigin; // A supprimer ?

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $propoResult;

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

    public function getMandateDate(): ?\DateTimeInterface
    {
        return $this->mandateDate;
    }

    public function setMandateDate(?\DateTimeInterface $mandateDate): self
    {
        $this->mandateDate = $mandateDate;

        return $this;
    }

    public function getCityOrigin(): ?string
    {
        return $this->cityOrigin;
    }

    public function setCityOrigin(?string $cityOrigin): self
    {
        $this->cityOrigin = $cityOrigin;

        return $this;
    }

    public function getPropoHousing(): ?int
    {
        return $this->propoHousing;
    }

    public function getPropoHousingToString(): ?string
    {
        return $this->getPropoHousing() ? Choices::YES_NO[$this->getPropoHousing()] : null;
    }

    public function setPropoHousing(?int $propoHousing): self
    {
        $this->propoHousing = $propoHousing;

        return $this;
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

    public function getReadyToHousing(): ?int
    {
        return $this->readyToHousing;
    }

    public function getReadyToHousingToString(): ?string
    {
        return $this->getReadyToHousing() ? Choices::YES_NO[$this->getReadyToHousing()] : null;
    }

    public function setReadyToHousing(?int $readyToHousing): self
    {
        $this->readyToHousing = $readyToHousing;

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

    public function getPropoOrigin(): ?int
    {
        return $this->propoOrigin;
    }

    public function getPropoOriginToString(): ?string
    {
        return $this->getPropoOrigin() ? self::PROPO_ORIGIN[$this->getPropoOrigin()] : null;
    }

    public function setPropoOrigin(?int $propoOrigin): self
    {
        $this->propoOrigin = $propoOrigin;

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
