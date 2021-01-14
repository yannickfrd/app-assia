<?php

namespace App\Entity\Support;

use App\Entity\Evaluation\EvalHousingGroup;
use App\Form\Utils\Choices;
use App\Repository\Support\HotelSupportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=HotelSupportRepository::class)
 */
class HotelSupport
{
    public const LEVEL_SUPPORT = [
        1 => 'Evaluation (1)',
        2 => 'Global (1)',
        3 => 'Complémentarité (0,5)',
        4 => 'Veille sociale (0,3)',
    ];

    public const END_SUPPORT_REASON = [
        1 => 'Accès à une solution d\'hébgt/logt',
        2 => 'Non respect de la convention d\'acc.',
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
     * @Groups("export")
     */
    private $ssd;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $evaluationDate;

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
     * @Groups("export")
     */
    private $agreementDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $departmentAnchor;

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

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $accessId;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $amhId;

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
        return $this->getOriginDept() ? Choices::DEPARTMENTS[$this->getOriginDept()] : null;
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

    /**
     * @Groups("export")
     */
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

    /**
     * @Groups("export")
     */
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

    /**
     * @Groups("export")
     */
    public function getDepartmentAnchorToString(): ?string
    {
        return $this->getDepartmentAnchor() ? Choices::DEPARTMENTS[$this->getDepartmentAnchor()] : null;
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

    /**
     * @Groups("export")
     */
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

    public function getAccessId(): ?int
    {
        return $this->accessId;
    }

    public function setAccessId(?int $accessId): self
    {
        $this->accessId = $accessId;

        return $this;
    }

    public function getAmhId(): ?int
    {
        return $this->amhId;
    }

    public function setAmhId(?int $amhId): self
    {
        $this->amhId = $amhId;

        return $this;
    }
}
