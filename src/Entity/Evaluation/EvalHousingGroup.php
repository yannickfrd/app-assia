<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\EvaluationChoices;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalHousingGroupRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalHousingGroup
{
    use SoftDeleteableEntity;

    public const HOUSING_STATUS = [
        001 => 'A la rue / abri de fortune',
        004 => 'Camp / Bidonville',
        400 => 'CADA',
        304 => 'Colocation',
        500 => 'Détention',
        105 => 'Dispositif hivernal',
        602 => 'Dispositif médical (LAM, autre)',
        003 => 'Errance résidentielle',
        502 => 'DLSAP',
        010 => 'Hébergé chez des tiers',
        011 => 'Hébergé chez famille',
        100 => 'Hôtel 115',
        101 => 'Hôtel (hors 115)',
        102 => 'Hébergement d’urgence',
        103 => 'Hébergement de stabilisation',
        104 => 'Hébergement d’insertion',
        600 => 'Hôpital',
        401 => 'HUDA',
        601 => 'LHSS',
        200 => 'Logement adapté - ALT',
        201 => 'Logement adapté - FJT',
        202 => 'Logement adapté - FTM',
        203 => 'Logement adapté - Maison relais',
        204 => 'Logement adapté - Résidence sociale',
        205 => 'Logement adapté - RHVS',
        206 => 'Logement adapté - Solibail/IML',
        207 => 'Logement foyer',
        300 => 'Logement privé',
        301 => 'Logement social',
        501 => 'Placement extérieur',
        303 => "Propriétaire d'un logement",
        302 => 'Sous-location',
        002 => 'Squat',
        97 => 'Autre',
        99 => 'Non évaluée',
    ];

    public const SIAO_RECOMMENDATION = [
        10 => 'Hébergement',
        102 => 'CHU',
        104 => 'CHRS',
        208 => 'ALTHO',
        20 => 'Logement adapté/ accompagné (hors Solibail)',
        206 => 'Solibail',
        30 => 'Logement de droit commun (social ou privé)',
        400 => 'CADA - dispositif asile',
        602 => 'Structure de soin ou médical',
        99 => 'Non évaluée',
    ];

    public const DALO_TYPE = [
        1 => 'Hébergement',
        2 => 'Logement',
        3 => 'DALO requalifié hébergement',
        99 => 'Non évalué',
    ];

    public const HOUSING_HELPS = [
        'fsl' => 'Fonds de solidarité pour le logement (FSL)',
        'fslEligibility' => 'Eligibilité aide à l\'installation FSL',
        'cafEligibility' => 'Eligibilité CAF',
        'otherHelps' => 'Autre(s) aide(s)',
    ];

    public const DOMICILIATION_TYPE = [
        1 => 'CCAS',
        2 => 'Organisme agréé (association)',
        3 => 'Chez un tiers ou famille',
        4 => 'Hôtel',
        97 => 'Autre',
        99 => 'Non évalué',
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
    private $siaoRequest;

    /** @Groups("export") */
    private $siaoRequestToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $siaoRequestDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $siaoUpdatedRequestDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $siaoRequestDept;

    /** @Groups("export") */
    private $siaoRequestDeptToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $siaoRecommendation;

    /** @Groups("export") */
    private $siaoRecommendationToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialHousingRequest;

    /** @Groups("export") */
    private $socialHousingRequestToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $socialHousingRequestId;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $socialHousingRequestDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $socialHousingUpdatedRequestDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $housingWishes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $citiesWishes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $specificities;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $syplo;

    /** @Groups("export") */
    private $syploToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("export")
     */
    private $syploId;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $syploDate;

    /**
     * @ORM\Column(name="dalo_commission", type="smallint", nullable=true)
     */
    private $daloAction;

    /** @Groups("export") */
    private $daloActionToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("export")
     */
    private $daloId;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $daloRecordDate;

    /**
     * @ORM\Column(name="dalo_requalified_daho", type="smallint", nullable=true)
     */
    private $daloType;

    /** @Groups("export") */
    private $daloTypeToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $daloDecisionDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $daloTribunalAction;

    /** @Groups("export") */
    private $daloTribunalActionToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $daloTribunalActionDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $collectiveAgreementHousing;

    /** @Groups("export") */
    private $collectiveAgreementHousingToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $collectiveAgreementHousingDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $hsgActionEligibility;

    /** @Groups("export") */
    private $hsgActionEligibilityToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $hsgActionRecord;

    /** @Groups("export") */
    private $hsgActionRecordToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $hsgActionDate;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $hsgActionDept;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hsgActionRecordId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $expulsionInProgress;

    /** @Groups("export") */
    private $expulsionInProgressToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $publicForce;

    /** @Groups("export") */
    private $publicForceToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $publicForceDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $expulsionComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $housingExperience;

    /** @Groups("export") */
    private $housingExperienceToString;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $housingExpeComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $fsl;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $fslEligibility;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $cafEligibility;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $otherHelps;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hepsPrecision;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalHousing;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $housingStatus;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $housing;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $housingAddress;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $housingCity;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $housingDept;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $domiciliation;

    /** @Groups("export") */
    private $domiciliationToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $domiciliationType;

    /** @Groups("export") */
    private $domiciliationTypeToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $startDomiciliationDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $endDomiciliationDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $domiciliationAddress;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("export")
     */
    private $domiciliationCity;

    /**
     * @ORM\Column(name="domiciliation_dept", type="string", length=10, nullable=true)
     */
    private $domiciliationZipcode;

    /** @Groups("export") */
    private $domiciliationDept;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $domiciliationComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("export")
     */
    private $housingAccessType;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $housingArrivalDate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvaluationGroup", inversedBy="evalHousingGroup", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $evaluationGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiaoRequest(): ?int
    {
        return $this->siaoRequest;
    }

    public function getSiaoRequestToString(): ?string
    {
        return $this->siaoRequest ? EvaluationChoices::YES_NO_IN_PROGRESS_NC[$this->siaoRequest] : null;
    }

    public function setSiaoRequest(?int $siaoRequest): self
    {
        $this->siaoRequest = $siaoRequest;

        return $this;
    }

    public function getSiaoRequestDate(): ?\DateTimeInterface
    {
        return $this->siaoRequestDate;
    }

    public function setSiaoRequestDate(?\DateTimeInterface $siaoRequestDate): self
    {
        $this->siaoRequestDate = $siaoRequestDate;

        return $this;
    }

    public function getSiaoUpdatedRequestDate(): ?\DateTimeInterface
    {
        return $this->siaoUpdatedRequestDate;
    }

    public function setSiaoUpdatedRequestDate(?\DateTimeInterface $siaoUpdatedRequestDate): self
    {
        $this->siaoUpdatedRequestDate = $siaoUpdatedRequestDate;

        return $this;
    }

    public function getSiaoRequestDept(): ?int
    {
        return $this->siaoRequestDept;
    }

    public function getSiaoRequestDeptToString(): ?string
    {
        return $this->siaoRequestDept ? EvaluationChoices::DEPARTMENTS[$this->siaoRequestDept] : null;
    }

    public function setSiaoRequestDept(?int $siaoRequestDept): self
    {
        $this->siaoRequestDept = $siaoRequestDept;

        return $this;
    }

    public function getSiaoRecommendation(): ?int
    {
        return $this->siaoRecommendation;
    }

    public function getSiaoRecommendationToString(): ?string
    {
        return $this->siaoRecommendation ? self::SIAO_RECOMMENDATION[$this->siaoRecommendation] : null;
    }

    public function setSiaoRecommendation(?int $siaoRecommendation): self
    {
        $this->siaoRecommendation = $siaoRecommendation;

        return $this;
    }

    public function getSocialHousingRequest(): ?int
    {
        return $this->socialHousingRequest;
    }

    public function getSocialHousingRequestToString(): ?string
    {
        return $this->socialHousingRequest ? EvaluationChoices::YES_NO_IN_PROGRESS_NC[$this->socialHousingRequest] : null;
    }

    public function setSocialHousingRequest(?int $socialHousingRequest): self
    {
        $this->socialHousingRequest = $socialHousingRequest;

        return $this;
    }

    public function getSocialHousingRequestId(): ?string
    {
        return $this->socialHousingRequestId;
    }

    public function setSocialHousingRequestId(?string $socialHousingRequestId): self
    {
        $this->socialHousingRequestId = $socialHousingRequestId;

        return $this;
    }

    public function getSocialHousingRequestDate(): ?\DateTimeInterface
    {
        return $this->socialHousingRequestDate;
    }

    public function setSocialHousingRequestDate(?\DateTimeInterface $socialHousingRequestDate): self
    {
        $this->socialHousingRequestDate = $socialHousingRequestDate;

        return $this;
    }

    public function getSocialHousingUpdatedRequestDate(): ?\DateTimeInterface
    {
        return $this->socialHousingUpdatedRequestDate;
    }

    public function setSocialHousingUpdatedRequestDate(?\DateTimeInterface $socialHousingUpdatedRequestDate): self
    {
        $this->socialHousingUpdatedRequestDate = $socialHousingUpdatedRequestDate;

        return $this;
    }

    public function getHousingWishes(): ?string
    {
        return $this->housingWishes;
    }

    public function setHousingWishes(?string $housingWishes): self
    {
        $this->housingWishes = $housingWishes;

        return $this;
    }

    public function getCitiesWishes(): ?string
    {
        return $this->citiesWishes;
    }

    public function setCitiesWishes(?string $citiesWishes): self
    {
        $this->citiesWishes = $citiesWishes;

        return $this;
    }

    public function getSpecificities(): ?string
    {
        return $this->specificities;
    }

    public function setSpecificities(?string $specificities): self
    {
        $this->specificities = $specificities;

        return $this;
    }

    public function getSyplo(): ?int
    {
        return $this->syplo;
    }

    public function getSyploToString(): ?string
    {
        return $this->syplo ? EvaluationChoices::YES_NO_IN_PROGRESS_NC[$this->syplo] : null;
    }

    public function setSyplo(?int $syplo): self
    {
        $this->syplo = $syplo;

        return $this;
    }

    public function getSyploId(): ?string
    {
        return $this->syploId;
    }

    public function setSyploId(?string $syploId): self
    {
        $this->syploId = $syploId;

        return $this;
    }

    public function getSyploDate(): ?\DateTimeInterface
    {
        return $this->syploDate;
    }

    public function setSyploDate(?\DateTimeInterface $syploDate): self
    {
        $this->syploDate = $syploDate;

        return $this;
    }

    public function getDaloAction(): ?int
    {
        return $this->daloAction;
    }

    public function getDaloActionToString(): ?string
    {
        return $this->daloAction ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->daloAction] : null;
    }

    public function setDaloAction(?int $daloAction): self
    {
        $this->daloAction = $daloAction;

        return $this;
    }

    public function getDaloId(): ?string
    {
        return $this->daloId;
    }

    public function setDaloId(?string $daloId): self
    {
        $this->daloId = $daloId;

        return $this;
    }

    public function getDaloRecordDate(): ?\DateTimeInterface
    {
        return $this->daloRecordDate;
    }

    public function setDaloRecordDate(?\DateTimeInterface $daloRecordDate): self
    {
        $this->daloRecordDate = $daloRecordDate;

        return $this;
    }

    public function getDaloType(): ?int
    {
        return $this->daloType;
    }

    public function getDaloTypeToString(): ?string
    {
        return $this->daloType ? self::DALO_TYPE[$this->daloType] : null;
    }

    public function setDaloType(?int $daloType): self
    {
        $this->daloType = $daloType;

        return $this;
    }

    public function getDaloDecisionDate(): ?\DateTimeInterface
    {
        return $this->daloDecisionDate;
    }

    public function setDaloDecisionDate(?\DateTimeInterface $daloDecisionDate): self
    {
        $this->daloDecisionDate = $daloDecisionDate;

        return $this;
    }

    public function getDaloTribunalAction(): ?int
    {
        return $this->daloTribunalAction;
    }

    public function getDaloTribunalActionToString(): ?string
    {
        return $this->daloTribunalAction ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->daloTribunalAction] : null;
    }

    public function setDaloTribunalAction(?int $daloTribunalAction): self
    {
        $this->daloTribunalAction = $daloTribunalAction;

        return $this;
    }

    public function getDaloTribunalActionDate(): ?\DateTimeInterface
    {
        return $this->daloTribunalActionDate;
    }

    public function setDaloTribunalActionDate(?\DateTimeInterface $daloTribunalActionDate): self
    {
        $this->daloTribunalActionDate = $daloTribunalActionDate;

        return $this;
    }

    public function getCollectiveAgreementHousing(): ?int
    {
        return $this->collectiveAgreementHousing;
    }

    public function getCollectiveAgreementHousingToString(): ?string
    {
        return $this->collectiveAgreementHousing ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->collectiveAgreementHousing] : null;
    }

    public function setCollectiveAgreementHousing(?int $collectiveAgreementHousing): self
    {
        $this->collectiveAgreementHousing = $collectiveAgreementHousing;

        return $this;
    }

    public function getCollectiveAgreementHousingDate(): ?\DateTimeInterface
    {
        return $this->collectiveAgreementHousingDate;
    }

    public function setCollectiveAgreementHousingDate(?\DateTimeInterface $collectiveAgreementHousingDate): self
    {
        $this->collectiveAgreementHousingDate = $collectiveAgreementHousingDate;

        return $this;
    }

    public function getHsgActionEligibility(): ?int
    {
        return $this->hsgActionEligibility;
    }

    public function getHsgActionEligibilityToString(): ?string
    {
        return $this->hsgActionEligibility ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->hsgActionEligibility] : null;
    }

    public function setHsgActionEligibility(?int $hsgActionEligibility): self
    {
        $this->hsgActionEligibility = $hsgActionEligibility;

        return $this;
    }

    public function getHsgActionRecord(): ?int
    {
        return $this->hsgActionRecord;
    }

    public function getHsgActionRecordToString(): ?string
    {
        return $this->hsgActionRecord ? EvaluationChoices::YES_NO[$this->hsgActionRecord] : null;
    }

    public function setHsgActionRecord(?int $hsgActionRecord): self
    {
        $this->hsgActionRecord = $hsgActionRecord;

        return $this;
    }

    public function getHsgActionDate(): ?\DateTimeInterface
    {
        return $this->hsgActionDate;
    }

    public function setHsgActionDate(?\DateTimeInterface $hsgActionDate): self
    {
        $this->hsgActionDate = $hsgActionDate;

        return $this;
    }

    public function getHsgActionDept(): ?string
    {
        return $this->hsgActionDept;
    }

    public function setHsgActionDept(?string $hsgActionDept): self
    {
        $this->hsgActionDept = $hsgActionDept;

        return $this;
    }

    public function getHsgActionRecordId(): ?string
    {
        return $this->hsgActionRecordId;
    }

    public function setHsgActionRecordId(?string $hsgActionRecordId): self
    {
        $this->hsgActionRecordId = $hsgActionRecordId;

        return $this;
    }

    public function getExpulsionInProgress(): ?int
    {
        return $this->expulsionInProgress;
    }

    public function getExpulsionInProgressToString(): ?string
    {
        return $this->expulsionInProgress ? EvaluationChoices::YES_NO[$this->expulsionInProgress] : null;
    }

    public function setExpulsionInProgress(?int $expulsionInProgress): self
    {
        $this->expulsionInProgress = $expulsionInProgress;

        return $this;
    }

    public function getPublicForce(): ?int
    {
        return $this->publicForce;
    }

    public function getPublicForceToString(): ?string
    {
        return $this->publicForce ? EvaluationChoices::YES_NO[$this->publicForce] : null;
    }

    public function setPublicForce(?int $publicForce): self
    {
        $this->publicForce = $publicForce;

        return $this;
    }

    public function getPublicForceDate(): ?\DateTimeInterface
    {
        return $this->publicForceDate;
    }

    public function setPublicForceDate(?\DateTimeInterface $publicForceDate): self
    {
        $this->publicForceDate = $publicForceDate;

        return $this;
    }

    public function getExpulsionComment(): ?string
    {
        return $this->expulsionComment;
    }

    public function setExpulsionComment(?string $expulsionComment): self
    {
        $this->expulsionComment = $expulsionComment;

        return $this;
    }

    public function getHousingExperience(): ?int
    {
        return $this->housingExperience;
    }

    public function getHousingExperienceToString(): ?string
    {
        return $this->housingExperience ? EvaluationChoices::YES_NO[$this->housingExperience] : null;
    }

    public function setHousingExperience(?int $housingExperience): self
    {
        $this->housingExperience = $housingExperience;

        return $this;
    }

    public function getHousingExpeComment(): ?string
    {
        return $this->housingExpeComment;
    }

    public function setHousingExpeComment(?string $housingExpeComment): self
    {
        $this->housingExpeComment = $housingExpeComment;

        return $this;
    }

    public function getHousingHelps(): array
    {
        return self::HOUSING_HELPS;
    }

    public function getFsl(): ?int
    {
        return $this->fsl;
    }

    public function getFslToString(): ?string
    {
        return $this->fsl ? EvaluationChoices::YES_NO_BOOLEAN[$this->fsl] : null;
    }

    public function setFsl(?int $fsl): self
    {
        $this->fsl = $fsl;

        return $this;
    }

    public function getFslEligibility(): ?int
    {
        return $this->fslEligibility;
    }

    public function getFslEligibilityToString(): ?string
    {
        return $this->fslEligibility ? EvaluationChoices::YES_NO_BOOLEAN[$this->fslEligibility] : null;
    }

    public function setFslEligibility(?int $fslEligibility): self
    {
        $this->fslEligibility = $fslEligibility;

        return $this;
    }

    public function getCafEligibility(): ?int
    {
        return $this->cafEligibility;
    }

    public function getCafEligibilityToString(): ?string
    {
        return $this->cafEligibility ? EvaluationChoices::YES_NO_BOOLEAN[$this->cafEligibility] : null;
    }

    public function setCafEligibility(?int $cafEligibility): self
    {
        $this->cafEligibility = $cafEligibility;

        return $this;
    }

    public function getOtherHelps(): ?int
    {
        return $this->otherHelps;
    }

    public function getOtherHelpsToString(): ?string
    {
        return $this->otherHelps ? EvaluationChoices::YES_NO_BOOLEAN[$this->otherHelps] : null;
    }

    public function setOtherHelps(?int $otherHelps): self
    {
        $this->otherHelps = $otherHelps;

        return $this;
    }

    public function getHepsPrecision(): ?string
    {
        return $this->hepsPrecision;
    }

    public function setHepsPrecision(?string $hepsPrecision): self
    {
        $this->hepsPrecision = $hepsPrecision;

        return $this;
    }

    public function getCommentEvalHousing(): ?string
    {
        return $this->commentEvalHousing;
    }

    public function setCommentEvalHousing(?string $commentEvalHousing): self
    {
        $this->commentEvalHousing = $commentEvalHousing;

        return $this;
    }

    public function getHousingStatus(): ?int
    {
        return $this->housingStatus;
    }

    public function getHousingStatusToString(): ?string
    {
        return $this->housingStatus ? self::HOUSING_STATUS[$this->housingStatus] : null;
    }

    public function setHousingStatus(?int $housingStatus): self
    {
        $this->housingStatus = $housingStatus;

        return $this;
    }

    public function getHousing(): ?int
    {
        return $this->housing;
    }

    public function getHousingToString(): ?string
    {
        return $this->housing ? EvaluationChoices::YES_NO[$this->housing] : null;
    }

    public function setHousing(?int $housing): self
    {
        $this->housing = $housing;

        return $this;
    }

    public function getHousingAddress(): ?string
    {
        return $this->housingAddress;
    }

    public function setHousingAddress(?string $housingAddress): self
    {
        $this->housingAddress = $housingAddress;

        return $this;
    }

    public function getHousingCity(): ?string
    {
        return $this->housingCity;
    }

    public function setHousingCity(?string $housingCity): self
    {
        $this->housingCity = $housingCity;

        return $this;
    }

    public function getHousingDept(): ?string
    {
        return $this->housingDept;
    }

    public function setHousingDept(?string $housingDept): self
    {
        $this->housingDept = $housingDept;

        return $this;
    }

    public function getDomiciliation(): ?int
    {
        return $this->domiciliation;
    }

    public function getDomiciliationToString(): ?string
    {
        return $this->domiciliation ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->domiciliation] : null;
    }

    public function setDomiciliation(?int $domiciliation): self
    {
        $this->domiciliation = $domiciliation;

        return $this;
    }

    public function getDomiciliationType(): ?int
    {
        return $this->domiciliationType;
    }

    public function getDomiciliationTypeToString(): ?string
    {
        return $this->domiciliationType ? self::DOMICILIATION_TYPE[$this->domiciliationType] : null;
    }

    public function setDomiciliationType(?int $domiciliationType): self
    {
        $this->domiciliationType = $domiciliationType;

        return $this;
    }

    public function getStartDomiciliationDate(): ?\DateTimeInterface
    {
        return $this->startDomiciliationDate;
    }

    public function setStartDomiciliationDate(?\DateTimeInterface $startDomiciliationDate): self
    {
        $this->startDomiciliationDate = $startDomiciliationDate;

        return $this;
    }

    public function getEndDomiciliationDate(): ?\DateTimeInterface
    {
        return $this->endDomiciliationDate;
    }

    public function setEndDomiciliationDate(?\DateTimeInterface $endDomiciliationDate): self
    {
        $this->endDomiciliationDate = $endDomiciliationDate;

        return $this;
    }

    public function getDomiciliationAddress(): ?string
    {
        return $this->domiciliationAddress;
    }

    public function setDomiciliationAddress(?string $domiciliationAddress): self
    {
        $this->domiciliationAddress = $domiciliationAddress;

        return $this;
    }

    public function getDomiciliationCity(): ?string
    {
        return $this->domiciliationCity;
    }

    public function setDomiciliationCity(?string $domiciliationCity): self
    {
        $this->domiciliationCity = $domiciliationCity;

        return $this;
    }

    public function getDomiciliationZipcode(): ?string
    {
        return $this->domiciliationZipcode;
    }

    public function setDomiciliationZipcode(?string $domiciliationZipcode): self
    {
        $this->domiciliationZipcode = $domiciliationZipcode;

        return $this;
    }

    public function getDomiciliationDept(): ?string
    {
        return substr($this->domiciliationZipcode, 0, 2);
    }

    public function getDomiciliationComment(): ?string
    {
        return $this->domiciliationComment;
    }

    public function setDomiciliationComment(?string $domiciliationComment): self
    {
        $this->domiciliationComment = $domiciliationComment;

        return $this;
    }

    public function getHousingAccessType(): ?int
    {
        return $this->housingAccessType;
    }

    public function setHousingAccessType(?int $housingAccessType): self
    {
        $this->housingAccessType = $housingAccessType;

        return $this;
    }

    public function getHousingArrivalDate(): ?\DateTimeInterface
    {
        return $this->housingArrivalDate;
    }

    public function setHousingArrivalDate(?\DateTimeInterface $housingArrivalDate): self
    {
        $this->housingArrivalDate = $housingArrivalDate;

        return $this;
    }

    public function getEvaluationGroup(): ?EvaluationGroup
    {
        return $this->evaluationGroup;
    }

    public function setEvaluationGroup(EvaluationGroup $evaluationGroup): self
    {
        $this->evaluationGroup = $evaluationGroup;

        return $this;
    }
}
