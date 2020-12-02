<?php

namespace App\Entity;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Repository\ContributionRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ContributionRepository::class)
 */
class Contribution
{
    use CreatedUpdatedEntityTrait;

    public const TYPE_CONTRIBUTION = 1;
    public const TYPE_RENT = 2;
    public const TYPE_BAIL = 10;
    public const TYPE_LOAN = 20;
    public const TYPE_REFUND_CONTRIB = 30;
    public const TYPE_REFUND_BAIL = 31;
    public const TYPE_REFUND_LOAN = 32;
    public const TYPE_RETURN_BAIL = 11;

    public const CONTRIBUTION_TYPE = [
        1 => 'Participation / Redevance', // 1
        2 => 'Loyer', // 1
        10 => 'Caution', // ex 2
        20 => 'Prêt / Avance', // ex 3
        30 => 'Rembt dette | PF / Loyer', // ex 11
        31 => 'Rembt dette | Caution', // ex 12
        32 => 'Rembt dette | Prêt', // ex 13
        11 => 'Restitution Caution', // ex 22
    ];

    public const DEFAULT_CONTRIBUTION_TYPE = 1;

    public const PAYMENT_TYPE = [
        1 => 'Virement',
        3 => 'Chèque',
        4 => 'Espèce',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("get")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups("get")
     */
    private $type = self::DEFAULT_CONTRIBUTION_TYPE;

    /**
     * @Groups("export")
     */
    private $typeToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("get")
     */
    private $monthContrib;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $resourcesAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $salaryAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $rentAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $aplAmt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"get", "export"})
     */
    private $credential;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $toPayAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("get")
     */
    private $paymentType;

    /**
     * @Groups("export")
     */
    private $paymentTypeToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $paidAmt;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"get", "export"})
     */
    private $paymentDate;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $stillToPayAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"get", "export"})
     */
    private $returnAmt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"get", "export"})
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity=SupportGroup::class, inversedBy="contributions")
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
        return $this->type ? self::CONTRIBUTION_TYPE[$this->type] : null;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMonthContrib(): ?\DateTimeInterface
    {
        return $this->monthContrib;
    }

    public function setMonthContrib(?\DateTimeInterface $monthContrib): self
    {
        $this->monthContrib = $monthContrib;

        return $this;
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

    public function getSalaryAmt(): ?float
    {
        return $this->salaryAmt;
    }

    public function setSalaryAmt(?float $salaryAmt): self
    {
        $this->salaryAmt = $salaryAmt;

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
        return $this->paymentType ? self::PAYMENT_TYPE[$this->paymentType] : null;
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

    public function setReturnAmt(?float $returnAmt): self
    {
        $this->returnAmt = $returnAmt;

        return $this;
    }

    public function getStartDate(): ?DateTime
    {
        return max($this->monthContrib, $this->supportGroup->getStartDate());
    }

    public function getEndDate(): ?DateTime
    {
        if ($this->monthContrib) {
            $lastDay = (clone $this->monthContrib)->modify('last day of this month');

            return min($lastDay, $this->supportGroup->getEndDate() ?? $lastDay);
        }

        return null;
    }

    public function getNbDays(): ?int
    {
        return $this->monthContrib ? $this->getStartDate()->diff($this->getEndDate())->days + 1 : null;
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
}
