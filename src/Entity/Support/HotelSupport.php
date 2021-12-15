<?php

namespace App\Entity\Support;

use App\Entity\Evaluation\EvalHousingGroup;
use App\Form\Utils\Choices;
use App\Repository\Support\HotelSupportRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HotelSupportRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class HotelSupport
{
    use SoftDeleteableEntity;

    public const STATUS = [
        2 => 'En cours', // Inclusion effective
        4 => 'Terminé', // Fin d\'accompagnement
        5 => 'Non abouti',
        6 => 'Liste d\'attente',
        3 => 'Suspendu',
        97 => 'Autre',
    ];

    public const REASON_NO_INCLUSION = [
        1 => 'Ménage injoignable',
        2 => 'Absence du ménage',
        3 => 'Refus du ménage',
        4 => 'Erreur d\'orientation',
        5 => 'Arrêt de prise en charge',
        97 => 'Autre motif',
    ];

    public const EMERGENCY_ACTION_REQUEST = [
        1 => 'Problèmes liés à la parentalité',
        2 => 'Violences familiales',
        3 => 'Problèmes liés à l\'alimentation',
        4 => 'Problèmes de santé',
        97 => 'Autres critères de vulnérabilité',
    ];

    public const EMERGENCY_ACTION_DONE = [
        1 => 'Signalement',
        2 => 'Décohabitation',
        3 => 'Colis alimentaire',
        4 => 'Orientation partenaire',
        97 => 'Autre',
    ];

    public const SUPPORT_LEVELS = [
        1 => 'Évaluation', // (1)
        2 => 'Subsidiarité', // Global (1)
        3 => 'Complémentarité', // (0,5)
        4 => 'Veille sociale avec référent', // (0,3)
        5 => 'Veille sociale sans référent', // (0,3)
    ];

    public const END_SUPPORT_REASONS = [
        1 => 'Accès à une solution d\'hébgt/logt',
        6 => 'Fin d\'intervention d\'urgence',
        5 => 'Fin de prise en charge 115',
        3 => 'Fin de prise en charge ASE',
        2 => 'Non adhésion à l\'accompagnement',
        4 => 'Départ vers un autre département',
        97 => 'Autre',
        99 => 'Non évalué',
    ];

    public const RECOMMENDATIONS = EvalHousingGroup::SIAO_RECOMMENDATION;
    public const DEPARTMENTS = Choices::DEPARTMENTS;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $emergencyActionRequest;

    /** @Groups("export") */
    private $emergencyActionRequestToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $emergencyActionDone;

    /** @Groups("export") */
    private $emergencyActionDoneToString;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("export")
     */
    private $emergencyActionPrecision;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $reasonNoInclusion;

    /** @Groups("export") */
    private $reasonNoInclusionToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $entryHotelDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $originDept; // TO DELETE

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $gipId; // TO DELETE

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("export")
     */
    private $ssd;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $evaluationDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $agreementDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $levelSupport;

    /** @Groups("export") */
    private $levelSupportToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $departmentAnchor;

    /** @Groups("export") */
    private $departmentAnchorToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $recommendation;

    /** @Groups("export") */
    private $recommendationToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endSupportReason;

    /** @Groups("export") */
    private $endSupportReasonToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endSupportDepartment;

    /** @Groups("export") */
    private $endSupportDepartmentToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $endSupportComment;

    /**
     * @ORM\OneToOne(targetEntity=SupportGroup::class, inversedBy="hotelSupport", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rosalieId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatusToString(): ?string
    {
        return $this->supportGroup ? self::STATUS[$this->supportGroup->getStatus()] : null;
    }

    public function getReasonNoInclusion(): ?int
    {
        return $this->reasonNoInclusion;
    }

    public function getEmergencyActionRequest(): ?int
    {
        return $this->emergencyActionRequest;
    }

    public function getEmergencyActionRequestToString(): ?string
    {
        return $this->emergencyActionRequest ? self::EMERGENCY_ACTION_REQUEST[$this->emergencyActionRequest] : null;
    }

    public function setEmergencyActionRequest(?int $emergencyActionRequest): self
    {
        $this->emergencyActionRequest = $emergencyActionRequest;

        return $this;
    }

    public function getReasonNoInclusionToString(): ?string
    {
        return $this->reasonNoInclusion ? self::REASON_NO_INCLUSION[$this->reasonNoInclusion] : null;
    }

    public function setReasonNoInclusion(?int $reasonNoInclusion): self
    {
        $this->reasonNoInclusion = $reasonNoInclusion;

        return $this;
    }

    public function getEmergencyActionDone(): ?int
    {
        return $this->emergencyActionDone;
    }

    public function getEmergencyActionDoneToString(): ?string
    {
        return $this->emergencyActionDone ? self::EMERGENCY_ACTION_DONE[$this->emergencyActionDone] : null;
    }

    public function setEmergencyActionDone(?int $emergencyActionDone): self
    {
        $this->emergencyActionDone = $emergencyActionDone;

        return $this;
    }

    public function getEmergencyActionPrecision(): ?string
    {
        return $this->emergencyActionPrecision;
    }

    public function setEmergencyActionPrecision(?string $emergencyActionPrecision): self
    {
        $this->emergencyActionPrecision = $emergencyActionPrecision;

        return $this;
    }

    public function getEntryHotelDate(): ?\DateTimeInterface
    {
        return $this->entryHotelDate;
    }

    public function setEntryHotelDate(?\DateTimeInterface $entryHotelDate): self
    {
        $this->entryHotelDate = $entryHotelDate;

        return $this;
    }

    public function getSsd(): ?string
    {
        return $this->ssd;
    }

    public function setSsd(?string $ssd): self
    {
        $this->ssd = $ssd;

        return $this;
    }

    public function getEvaluationDate(): ?\DateTimeInterface
    {
        return $this->evaluationDate;
    }

    public function setEvaluationDate(?\DateTimeInterface $evaluationDate): self
    {
        $this->evaluationDate = $evaluationDate;

        return $this;
    }

    public function getLevelSupport(): ?int
    {
        return $this->levelSupport;
    }

    public function getLevelSupportToString(): ?string
    {
        return $this->levelSupport ? self::SUPPORT_LEVELS[$this->levelSupport] : null;
    }

    public function setLevelSupport(?int $levelSupport): self
    {
        $this->levelSupport = $levelSupport;

        return $this;
    }

    public function getRecommendation(): ?int
    {
        return $this->recommendation;
    }

    public function getRecommendationToString(): ?string
    {
        return $this->recommendation ? self::RECOMMENDATIONS[$this->recommendation] : null;
    }

    public function setRecommendation(?int $recommendation): self
    {
        $this->recommendation = $recommendation;

        return $this;
    }

    public function getAgreementDate(): ?\DateTimeInterface
    {
        return $this->agreementDate;
    }

    public function setAgreementDate(?\DateTimeInterface $agreementDate): self
    {
        $this->agreementDate = $agreementDate;

        return $this;
    }

    public function getDepartmentAnchor(): ?int
    {
        return $this->departmentAnchor;
    }

    public function getDepartmentAnchorToString(): ?string
    {
        return $this->departmentAnchor ? Choices::DEPARTMENTS[$this->departmentAnchor] : null;
    }

    public function setDepartmentAnchor(?int $departmentAnchor): self
    {
        $this->departmentAnchor = $departmentAnchor;

        return $this;
    }

    public function getEndSupportReason(): ?int
    {
        return $this->endSupportReason;
    }

    public function getEndSupportReasonToString(): ?string
    {
        return $this->endSupportReason ? self::END_SUPPORT_REASONS[$this->endSupportReason] : null;
    }

    public function setEndSupportReason(?int $endSupportReason): self
    {
        $this->endSupportReason = $endSupportReason;

        return $this;
    }

    public function getEndSupportDepartment(): ?int
    {
        return $this->endSupportDepartment;
    }

    public function getEndSupportDepartmentToString(): ?string
    {
        return $this->endSupportDepartment ? Choices::DEPARTMENTS[$this->endSupportDepartment] : null;
    }

    public function setEndSupportDepartment(?int $endSupportDepartment): self
    {
        $this->endSupportDepartment = $endSupportDepartment;

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

    public function getRosalieId(): ?string
    {
        return $this->rosalieId;
    }

    public function setRosalieId(?string $rosalieId): self
    {
        $this->rosalieId = $rosalieId;

        return $this;
    }
}
