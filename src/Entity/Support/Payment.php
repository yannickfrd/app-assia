<?php

namespace App\Entity\Support;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Form\Utils\Choices;
use App\Repository\Support\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PaymentRepository::class)
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Payment
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    public const CONTRIBUTION = 1;
    public const RENT = 2;
    public const DEPOSIT = 10;
    public const LOAN = 20;
    public const REPAYMENT = 30;
    public const DEPOSIT_REFUNT = 11;

    public const TYPES = [
        1 => 'Participation financière',
        2 => 'Loyer / Redevance',
        10 => 'Caution',
        20 => 'Prêt / Avance',
        30 => 'Remboursement',
        11 => 'Restitution de caution',
    ];

    public const CONTRIBUTION_HOTEL_TYPES = [
        1 => 'Participation financière',
        30 => 'Remboursement',
    ];

    public const REPAYMENT_REASONS = [
        1 => 'PF / Redevance',
        2 => 'Caution',
        3 => 'Prêt / Avance',
        4 => 'Divers',
    ];

    public const DEFAULT_TYPE = 1;

    public const PAYMENT_TYPES = [
        1 => 'Virement',
        // 2 => '',
        3 => 'Chèque',
        4 => 'Espèce',
        99 => 'Non renseigné',
    ];

    public const NO_CONTRIB_REASONS = [
        1 => 'Difficultés financières temporaires',
        2 => 'Attribution d\'un logement en cours',
    ];

    public const SERIALIZER_GROUPS = [
        'show_payment', 'show_support_group', 'show_created_updated', 'show_user'
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"get", "show_payment"})
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups({"get", "show_payment"})
     */
    private $type = self::DEFAULT_TYPE;

    /**
     * @Groups("export")
     */
    private $typeToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $endDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $resourcesAmt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $chargesAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $rentAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $aplAmt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $credential;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $toPayAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $paymentType;

    /**
     * @Groups("export")
     */
    private $paymentTypeToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $paidAmt;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $paymentDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $stillToPayAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $returnAmt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"get", "show_payment", "export"})
     */
    private $comment;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $commentExport;

    /**
     * @ORM\ManyToOne(targetEntity=SupportGroup::class, inversedBy="payments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $checkAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $pdfGenerateAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $mailSentAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $noContrib;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $noContribReason;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $nbConsumUnits;

    private $ratioNbDays;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "show_payment"})
     */
    private $contributionRate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $repaymentReason;

    /** @var float */
    private $theoricalContribAmt;

    /** @var float */
    private $restToLive;

    /**
     * @ORM\PreFlush
     */
    public function preFlush(): void
    {
        $this->setStillToPayAmt($this->getToPayAmt() - $this->getPaidAmt());

        if ($this->supportGroup) {
            $this->supportGroup->setUpdatedAt(new \DateTime());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getTypeToString(): ?string
    {
        return $this->type ? self::TYPES[$this->type] : null;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getNbDays(): ?int
    {
        if ($this->getEndDate() > $this->getStartDate()) {
            return $this->getStartDate()->diff($this->getEndDate())->days + 1;
        }

        return null;
    }

    public function getResourcesAmt(): ?float
    {
        return $this->resourcesAmt;
    }

    public function setResourcesAmt(?float $resourcesAmt): self
    {
        $this->resourcesAmt = $resourcesAmt;

        return $this;
    }

    public function getChargesAmt(): ?int
    {
        return $this->chargesAmt;
    }

    public function setChargesAmt(?int $chargesAmt): self
    {
        $this->chargesAmt = $chargesAmt;

        return $this;
    }

    public function getRentAmt(): ?float
    {
        return $this->rentAmt;
    }

    public function setRentAmt(?float $rentAmt): self
    {
        $this->rentAmt = $rentAmt;

        return $this;
    }

    public function getAplAmt(): ?float
    {
        return $this->aplAmt;
    }

    public function setAplAmt(?float $aplAmt): self
    {
        $this->aplAmt = $aplAmt;

        return $this;
    }

    public function getCredential(): ?string
    {
        return $this->credential;
    }

    public function setCredential(?string $credential): self
    {
        $this->credential = $credential;

        return $this;
    }

    public function getToPayAmt(): ?float
    {
        return $this->toPayAmt;
    }

    public function setToPayAmt(?float $toPayAmt): self
    {
        $this->toPayAmt = $toPayAmt;

        return $this;
    }

    public function getToPayAmtToString(): ?string
    {
        return $this->formatAmountToString($this->toPayAmt);
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(?\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getPaymentType(): ?int
    {
        return $this->paymentType;
    }

    public function getPaymentTypeToString(): ?string
    {
        return $this->paymentType ? self::PAYMENT_TYPES[$this->paymentType] : null;
    }

    public function setPaymentType(?int $paymentType): self
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    public function getPaidAmt(): ?float
    {
        return $this->paidAmt;
    }

    public function setPaidAmt(?float $paidAmt): self
    {
        $this->paidAmt = $paidAmt;

        return $this;
    }

    public function getPaidAmtToString(): ?string
    {
        return $this->formatAmountToString($this->paidAmt);
    }

    public function getStillToPayAmt(): ?float
    {
        return $this->getToPayAmt() - $this->getPaidAmt();
    }

    public function setStillToPayAmt(?float $stillToPayAmt = null): self
    {
        $this->stillToPayAmt = $this->getStillToPayAmt();

        return $this;
    }

    public function getReturnAmt(): ?float
    {
        return $this->returnAmt;
    }

    public function getReturnAmtToString(): ?string
    {
        return $this->formatAmountToString($this->returnAmt);
    }

    public function setReturnAmt(?float $returnAmt): self
    {
        $this->returnAmt = $returnAmt;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCommentExport(): ?string
    {
        return $this->commentExport;
    }

    public function setCommentExport(?string $commentExport): self
    {
        $this->commentExport = $commentExport;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }

    public function getCheckAt(): ?\DateTimeInterface
    {
        return $this->checkAt;
    }

    public function setCheckAt(?\DateTimeInterface $checkAt): self
    {
        $this->checkAt = $checkAt;

        return $this;
    }

    public function getPdfGenerateAt(): ?\DateTimeInterface
    {
        return $this->pdfGenerateAt;
    }

    public function setPdfGenerateAt(?\DateTimeInterface $pdfGenerateAt): self
    {
        $this->pdfGenerateAt = $pdfGenerateAt;

        return $this;
    }

    public function PdfGenerate(): bool
    {
        return null !== $this->pdfGenerateAt;
    }

    public function getMailSentAt(): ?\DateTimeInterface
    {
        return $this->mailSentAt;
    }

    public function setMailSentAt(?\DateTimeInterface $mailSentAt): self
    {
        $this->mailSentAt = $mailSentAt;

        return $this;
    }

    public function MailSent(): bool
    {
        return null !== $this->mailSentAt;
    }

    public function getNoContrib(): ?bool
    {
        return $this->noContrib;
    }

    public function getNoContribToString(): ?string
    {
        return $this->noContrib ? Choices::YES_NO_BOOLEAN[$this->noContrib] : null;
    }

    public function setNoContrib(?bool $noContrib): self
    {
        $this->noContrib = $noContrib;

        return $this;
    }

    public function getNoContribReason(): ?int
    {
        return $this->noContribReason;
    }

    /** @Groups({"get", "show_payment"}) */
    public function getNoContribReasonToString(): ?string
    {
        return $this->noContribReason ? self::NO_CONTRIB_REASONS[$this->noContribReason] : null;
    }

    public function setNoContribReason(?int $noContribReason): self
    {
        $this->noContribReason = $noContribReason;

        return $this;
    }

    public function getNbConsumUnits(): ?float
    {
        return $this->nbConsumUnits;
    }

    public function setNbConsumUnits(?float $nbConsumUnits): self
    {
        $this->nbConsumUnits = $nbConsumUnits;

        return $this;
    }

    public function getContributionRate(): ?float
    {
        return $this->contributionRate;
    }

    public function setContributionRate(?float $contributionRate): self
    {
        $this->contributionRate = $contributionRate;

        return $this;
    }

    public function getRatioNbDays(): ?float
    {
        return $this->ratioNbDays;
    }

    public function setRatioNbDays(?float $ratioNbDays): self
    {
        $this->ratioNbDays = $ratioNbDays;

        return $this;
    }

    public function getRepaymentReason(): ?int
    {
        return $this->repaymentReason;
    }

    public function setRepaymentReason(?int $repaymentReason): self
    {
        $this->repaymentReason = $repaymentReason;

        return $this;
    }

    public function getTheoricalContribAmt(): ?float
    {
        return $this->theoricalContribAmt;
    }

    public function setTheoricalContribAmt(?float $theoricalContribAmt): self
    {
        $this->theoricalContribAmt = $theoricalContribAmt;

        return $this;
    }

    public function getRestToLive(): ?float
    {
        return $this->restToLive;
    }

    public function setRestToLive(?float $restToLive): self
    {
        $this->restToLive = $restToLive;

        return $this;
    }

    private function formatAmountToString(?float $value)
    {
        return $value >= 0 ? (new \NumberFormatter('fr-FR', \NumberFormatter::SPELLOUT))->format($value) : null;
    }
}
