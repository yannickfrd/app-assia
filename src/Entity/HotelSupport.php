<?php

namespace App\Entity;

use App\Form\Utils\Choices;
use App\Entity\EvalHousingGroup;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\HotelSupportRepository;

/**
 * @ORM\Entity(repositoryClass=HotelSupportRepository::class)
 */
class HotelSupport
{
    public const ORIGIN_DEPT = [
        75 => '75',
        77 => '77',
        78 => '78',
        91 => '91',
        92 => '92',
        93 => '93',
        94 => '94',
        95 => '95',
        98 => 'Hors IDF',
    ];

    public const END_STATUS_DIAG = [
        1 => 'XXX',
        2 => 'XXX',
        3 => 'XXX',
        99 => 'Non renseigné',
    ];

    public const LEVEL_SUPPORT = [
        1 => 'Evaluation (1)',
        2 => 'Accompagnement global (1)',
        3 => 'Accompagnement en complémentarité (0,5)',
        4 => 'Veille sociale (0,3)',
    ];

    public const END_SUPPORT_REASON = [
        1 => 'Accès à une solution d\'hébgt/logt',
        2 => 'Non respect de la convention AMH',
        3 => 'Fin de prise en charge ASE',
        4 => 'Départ vers un autre département',
        97 => 'Autre',
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
    private $entryHotelDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $originDept;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $gipId;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $ssd;

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
    private $endStatusDiag;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $diagComment;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $supportStartDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $levelSupport;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $recommendation;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $agreementDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $departmentAnchor;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $supportEndDate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $supportComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endSupportReason;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $endSupportComment;

    /**
     * @ORM\OneToOne(targetEntity=SupportGroup::class, inversedBy="hotelSupport", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getOriginDept(): ?int
    {
        return $this->originDept;
    }

    public function getOriginDeptToString(): ?string
    {
        return $this->getOriginDept() ? self::ORIGIN_DEPT[$this->getOriginDept()] : null;
    }

    public function setOriginDept(?int $originDept): self
    {
        $this->originDept = $originDept;

        return $this;
    }

    public function getGipId(): ?string
    {
        return $this->gipId;
    }

    public function setGipId(?string $gipId): self
    {
        $this->gipId = $gipId;

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

    public function getEndStatusDiag(): ?int
    {
        return $this->endStatusDiag;
    }

    public function getEndStatusDiagToString(): ?string
    {
        return $this->getEndStatusDiag() ? self::END_STATUS_DIAG[$this->getEndStatusDiag()] : null;
    }

    public function setEndStatusDiag(?int $endStatusDiag): self
    {
        $this->endStatusDiag = $endStatusDiag;

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

    public function getLevelSupport(): ?int
    {
        return $this->levelSupport;
    }

    public function getLevelSupportToString(): ?string
    {
        return $this->getLevelSupport() ? self::LEVEL_SUPPORT[$this->getLevelSupport()] : null;
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
        return $this->getRecommendation() ? EvalHousingGroup::SIAO_RECOMMENDATION[$this->getRecommendation()] : null;
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
        return $this->getDepartmentAnchor() ? Choices::YES_NO[$this->getDepartmentAnchor()] : null;
    }

    public function setDepartmentAnchor(?int $departmentAnchor): self
    {
        $this->departmentAnchor = $departmentAnchor;

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
