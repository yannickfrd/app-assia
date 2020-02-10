<?php

namespace App\Entity;

use App\Form\Utils\SelectList;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SitHousingGroupRepository")
 */
class SitHousingGroup
{

    public const HOUSING_STATUS = [
        1 => "A la rue - abri de fortune",
        2 => "CADA",
        3 => "CHUDA",
        4 => "Colocation",
        5 => "Détention",
        6 => "Dispositif hivernal",
        7 => "Dispositif médical (LHSS, LAM, autre)",
        8 => "Errance résidentielle",
        9 => "Hébergé chez des tiers",
        10 => "Hébergé chez famille",
        11 => "Hôtel 115",
        12 => "Hôtel (hors 115)",
        13 => "Hébergement d’urgence",
        14 => "Hébergement de stabilisation",
        15 => "Hébergement d’insertion",
        16 => "Hôpital",
        17 => "Logement accompagné - ALT",
        18 => "Logement accompagné - FJT",
        19 => "Logement accompagné - FTM",
        20 => "Logement accompagné - RHVS",
        21 => "Logement accompagné - Solibail/IML",
        22 => "Logement foyer",
        23 => "Logement privé",
        24 => "Logement social",
        25 => "Résidence sociale",
        26 => "Maison relais",
        27 => "PEC- ASE",
        28 => "Squat",
        98 => "Autre",
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
    private $dls;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dlsId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dlsDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dlsRenewalDate;

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
     * @ORM\Column(type="date", nullable=true)
     */
    private $daloRecordDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $requalifiedDalo;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $decisionDate;

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
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $fsl;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $fslEligibility;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $cafEligibility;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $otherHelps;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $hepsPrecision;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentSitHousing;

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
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGroup", inversedBy="sitHousingGroup", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDls(): ?int
    {
        return $this->dls;
    }

    public function setDls(?int $dls): self
    {
        $this->dls = $dls;

        return $this;
    }

    public function getDlsList()
    {
        return SelectList::YES_NO[$this->dls];
    }

    public function getDlsId(): ?string
    {
        return $this->dlsId;
    }

    public function setDlsId(?string $dlsId): self
    {
        $this->dlsId = $dlsId;

        return $this;
    }

    public function getDlsDate(): ?\DateTimeInterface
    {
        return $this->dlsDate;
    }

    public function setDlsDate(?\DateTimeInterface $dlsDate): self
    {
        $this->dlsDate = $dlsDate;

        return $this;
    }

    public function getDlsRenewalDate(): ?\DateTimeInterface
    {
        return $this->dlsRenewalDate;
    }

    public function setDlsRenewalDate(?\DateTimeInterface $dlsRenewalDate): self
    {
        $this->dlsRenewalDate = $dlsRenewalDate;

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

    public function setSyplo(?int $syplo): self
    {
        $this->syplo = $syplo;

        return $this;
    }

    public function getSyploList()
    {
        return SelectList::YES_NO[$this->syplo];
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

    public function setDaloCommission(?int $daloCommission): self
    {
        $this->daloCommission = $daloCommission;

        return $this;
    }

    public function getDaloCommissionList()
    {
        return SelectList::YES_NO[$this->daloCommission];
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

    public function getRequalifiedDalo(): ?int
    {
        return $this->requalifiedDalo;
    }

    public function setRequalifiedDalo(?int $requalifiedDalo): self
    {
        $this->requalifiedDalo = $requalifiedDalo;

        return $this;
    }

    public function getRequalifiedDaloList()
    {
        return SelectList::YES_NO[$this->requalifiedDalo];
    }

    public function getDecisionDate(): ?\DateTimeInterface
    {
        return $this->decisionDate;
    }

    public function setDecisionDate(?\DateTimeInterface $decisionDate): self
    {
        $this->decisionDate = $decisionDate;

        return $this;
    }

    public function getHsgActionEligibility(): ?int
    {
        return $this->hsgActionEligibility;
    }

    public function setHsgActionEligibility(?int $hsgActionEligibility): self
    {
        $this->hsgActionEligibility = $hsgActionEligibility;

        return $this;
    }

    public function getHsgActionEligibilityList()
    {
        return SelectList::YES_NO[$this->syplo];
    }

    public function getHsgActionRecord(): ?int
    {
        return $this->hsgActionRecord;
    }

    public function setHsgActionRecord(?int $hsgActionRecord): self
    {
        $this->hsgActionRecord = $hsgActionRecord;

        return $this;
    }

    public function getHsgActionRecordList()
    {
        return SelectList::YES_NO[$this->hsgActionRecord];
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

    public function setExpulsionInProgress(?int $expulsionInProgress): self
    {
        $this->expulsionInProgress = $expulsionInProgress;

        return $this;
    }

    public function getExpulsionInProgressList()
    {
        return SelectList::YES_NO[$this->expulsionInProgress];
    }

    public function getPublicForce(): ?int
    {
        return $this->publicForce;
    }

    public function setPublicForce(?int $publicForce): self
    {
        $this->publicForce = $publicForce;

        return $this;
    }

    public function getPublicForceList()
    {
        return SelectList::YES_NO[$this->publicForce];
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

    public function setHousingExperience(?int $housingExperience): self
    {
        $this->housingExperience = $housingExperience;

        return $this;
    }

    public function getHousingExperienceList()
    {
        return SelectList::YES_NO[$this->housingExperience];
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

    public function getFsl(): ?bool
    {
        return $this->fsl;
    }

    public function setFsl(?bool $fsl): self
    {
        $this->fsl = $fsl;

        return $this;
    }

    public function getFslEligibility(): ?bool
    {
        return $this->fslEligibility;
    }

    public function setFslEligibility(?bool $fslEligibility): self
    {
        $this->fslEligibility = $fslEligibility;

        return $this;
    }

    public function getCafEligibility(): ?bool
    {
        return $this->cafEligibility;
    }

    public function setCafEligibility(?bool $cafEligibility): self
    {
        $this->cafEligibility = $cafEligibility;

        return $this;
    }

    public function getOtherHelps(): ?bool
    {
        return $this->otherHelps;
    }

    public function setOtherHelps(?bool $otherHelps): self
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

    public function getCommentSitHousing(): ?string
    {
        return $this->commentSitHousing;
    }

    public function setCommentSitHousing(?string $commentSitHousing): self
    {
        $this->commentSitHousing = $commentSitHousing;

        return $this;
    }

    public function getHousingStatus(): ?int
    {
        return $this->housingStatus;
    }

    public function setHousingStatus(?int $housingStatus): self
    {
        $this->housingStatus = $housingStatus;

        return $this;
    }

    public function getHousingStatusList()
    {
        return self::HOUSING_STATUS[$this->housingStatus];
    }


    public function getHousing(): ?int
    {
        return $this->housing;
    }

    public function setHousing(?int $housing): self
    {
        $this->housing = $housing;

        return $this;
    }

    public function getHousinglist()
    {
        return SelectList::YES_NO[$this->housing];
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

    public function getDomiciliationlist()
    {
        return SelectList::YES_NO[$this->domiciliation];
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
