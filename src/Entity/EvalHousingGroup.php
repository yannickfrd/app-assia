<?php

namespace App\Entity;

use App\Form\Utils\Choices;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalHousingGroupRepository")
 */
class EvalHousingGroup
{

    public const HOUSING_STATUS = [
        001 => "A la rue - abri de fortune",
        400 => "CADA",
        304 => "Colocation",
        500 => "Détention",
        105 => "Dispositif hivernal",
        602 => "Dispositif médical (LAM, autre)",
        003 => "Errance résidentielle",
        502 => "DLSAP",
        010 => "Hébergé chez des tiers",
        011 => "Hébergé chez famille",
        100 => "Hôtel 115",
        101 => "Hôtel (hors 115)",
        102 => "Hébergement d’urgence",
        103 => "Hébergement de stabilisation",
        104 => "Hébergement d’insertion",
        600 => "Hôpital",
        401 => "HUDA",
        601 => "LHSS",
        200 => "Logement adapté - ALT",
        201 => "Logement adapté - FJT",
        202 => "Logement adapté - FTM",
        203 => "Logement adapté - Maison relais",
        204 => "Logement adapté - Résidence sociale",
        205 => "Logement adapté - RHVS",
        206 => "Logement adapté - Solibail/IML",
        207 => "Logement foyer",
        300 => "Logement privé",
        301 => "Logement social",
        501 => "Placement extérieur",
        303 => "Propriétaire d'un logement",
        302 => "Sous-location",
        002 => "Squat",
        97 => "Autre",
        99 => "Non renseignée"
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

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $siaoRequestDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $siaoUpdatedRequestDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialHousingRequest;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $socialHousingRequestId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $socialHousingRequestDate;

    /**
     * @ORM\Column(type="date", nullable=true)
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

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $syploId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $syploDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $daloCommission;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $daloId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $daloRecordDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $daloTribunalAction;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $daloTribunalActionDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $daloRequalifiedDaho;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $daloDecisionDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $collectiveAgreementHousing;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $collectiveAgreementHousingDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $hsgActionEligibility;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $hsgActionRecord;

    /**
     * @ORM\Column(type="date", nullable=true)
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

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $publicForce;

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

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDomiciliationDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDomiciliationDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $domiciliationAddress;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $domiciliationCity;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $domiciliationDept;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $housingAccessType;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $housingArrivalDate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvaluationGroup", inversedBy="evalHousingGroup", cascade={"persist", "remove"})
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

    public function getSiaoRequestList()
    {
        return Choices::YES_NO_IN_PROGRESS_NC[$this->siaoRequest];
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

    public function getSocialHousingRequest(): ?int
    {
        return $this->socialHousingRequest;
    }

    public function getSocialHousingRequestList()
    {
        return Choices::YES_NO_IN_PROGRESS_NC[$this->socialHousingRequest];
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

    public function getSyploList()
    {
        return Choices::YES_NO_IN_PROGRESS[$this->syplo];
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

    public function getDaloCommission(): ?int
    {
        return $this->daloCommission;
    }

    public function getDaloCommissionList()
    {
        return Choices::YES_NO[$this->daloCommission];
    }

    public function setDaloCommission(?int $daloCommission): self
    {
        $this->daloCommission = $daloCommission;

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

    public function getDaloRequalifiedDaho(): ?int
    {
        return $this->daloRequalifiedDaho;
    }

    public function setDaloRequalifiedDaho(?int $daloRequalifiedDaho): self
    {
        $this->daloRequalifiedDaho = $daloRequalifiedDaho;

        return $this;
    }

    public function getDaloRequalifiedDahoList()
    {
        return Choices::YES_NO[$this->daloRequalifiedDaho];
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

    public function getDaloTribunalActionList()
    {
        return Choices::YES_NO_IN_PROGRESS[$this->daloTribunalAction];
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

    public function getCollectiveAgreementHousingList()
    {
        return Choices::YES_NO_IN_PROGRESS[$this->collectiveAgreementHousing];
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

    public function getHsgActionEligibilityList()
    {
        return Choices::YES_NO_IN_PROGRESS[$this->hsgActionEligibility];
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

    public function getHsgActionRecordList()
    {
        return Choices::YES_NO[$this->hsgActionRecord];
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

    public function getExpulsionInProgressList()
    {
        return Choices::YES_NO[$this->expulsionInProgress];
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

    public function getPublicForceList()
    {
        return Choices::YES_NO[$this->publicForce];
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

    public function getHousingExperienceList()
    {
        return Choices::YES_NO[$this->housingExperience];
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

    public function getFsl(): ?int
    {
        return $this->fsl;
    }

    public function getFslList()
    {
        return Choices::YES_NO_BOOLEAN[$this->fsl];
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

    public function getFslEligibilityList()
    {
        return Choices::YES_NO_BOOLEAN[$this->fslEligibility];
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

    public function getCafEligibilityList()
    {
        return Choices::YES_NO_BOOLEAN[$this->cafEligibility];
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

    public function getOtherHelpsList()
    {
        return Choices::YES_NO_BOOLEAN[$this->otherHelps];
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

    public function getHousingStatusList()
    {
        return self::HOUSING_STATUS[$this->housingStatus];
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

    public function getHousingList()
    {
        return Choices::YES_NO[$this->housing];
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

    public function setDomiciliation(?int $domiciliation): self
    {
        $this->domiciliation = $domiciliation;

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

    public function getDomiciliationList()
    {
        return Choices::YES_NO[$this->domiciliation];
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

    public function getDomiciliationDept(): ?string
    {
        return $this->domiciliationDept;
    }

    public function setDomiciliationDept(?string $domiciliationDept): self
    {
        $this->domiciliationDept = $domiciliationDept;

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
