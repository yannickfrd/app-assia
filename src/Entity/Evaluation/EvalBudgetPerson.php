<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\EvaluationChoices;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalBudgetPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalBudgetPerson
{
    use ResourcesEntityTrait;
    use SoftDeleteableEntity;

    public const RESOURCES = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Démarches en cours',
        4 => 'Droits supendus',
        99 => 'Non évalué',
    ];

    public const SETTLEMENT_PLAN = [
        1 => 'Proposé',
        2 => 'Accepté',
        3 => 'Refusé',
        4 => 'En cours',
        98 => 'Non concerné',
        99 => 'Non évalué',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @Groups("export") */
    private $resourceToString;
    /** @Groups("export") */
    private $evalBudgetResourcesToString;
    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $resourcesAmt;
    /** @Groups("export") */
    private $salariesAmt;
    /** @Groups("export") */
    private $areAmt;
    /** @Groups("export") */
    private $ijAmt;
    /** @Groups("export") */
    private $rsaAmt;
    /** @Groups("export") */
    private $afAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $incomeTax;

    /** @Groups("export") */
    private $incomeTaxToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $incomeN1Amt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $incomeN2Amt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $resourcesComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $charge;

    /** @Groups("export") */
    private $chargeToString;

    /** @Groups("export") */
    private $evalBudgetChargesToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $chargesAmt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $chargeComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debt;

    /** @Groups("export") */
    private $debtToString;

    /** @Groups("export") */
    private $evalBudgetDebtsToString;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("export")
     */
    private $debtsAmt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $debtComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $overIndebtRecord;

    /** @Groups("export") */
    private $overIndebtRecordToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $overIndebtRecordDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $settlementPlan;

    /** @Groups("export") */
    private $settlementPlanToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $moratorium;

    /** @Groups("export") */
    private $moratoriumToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endRightsDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalBudget;

    /**
     * @var Collection<EvalBudgetResource>|null
     * @ORM\OneToMany(targetEntity=EvalBudgetResource::class, mappedBy="evalBudgetPerson", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"amount": "DESC"})
     */
    private $evalBudgetResources;

    /**
     * @var Collection<EvalBudgetCharge>|null
     * @ORM\OneToMany(targetEntity=EvalBudgetCharge::class, mappedBy="evalBudgetPerson", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"amount": "DESC"})
     */
    private $evalBudgetCharges;

    /**
     * @var Collection<EvalBudgetDebt>|null
     * @ORM\OneToMany(targetEntity=EvalBudgetDebt::class, mappedBy="evalBudgetPerson", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"amount": "DESC"})
     */
    private $evalBudgetDebts;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvaluationPerson", inversedBy="evalBudgetPerson", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $evaluationPerson;

    public function __construct()
    {
        $this->evalBudgetResources = new ArrayCollection();
        $this->evalBudgetCharges = new ArrayCollection();
        $this->evalBudgetDebts = new ArrayCollection();
    }

    public function __clone()
    {
        $newEvalBudgetResources = new ArrayCollection();
        $newEvalBudgetCharges = new ArrayCollection();
        $newEvalBudgetDebts = new ArrayCollection();

        foreach ($this->evalBudgetResources as $evalBudgetResource) {
            $newEvalBudgetResource = clone $evalBudgetResource;
            $newEvalBudgetResource->setEvalBudgetPerson($this);
            $newEvalBudgetResources->add($newEvalBudgetResource);
        }

        foreach ($this->evalBudgetCharges as $evalBudgetCharge) {
            $newEvalBudgetCharge = clone $evalBudgetCharge;
            $newEvalBudgetCharge->setEvalBudgetPerson($this);
            $newEvalBudgetCharges->add($newEvalBudgetCharge);
        }

        foreach ($this->evalBudgetDebts as $evalBudgetDebt) {
            $newEvalBudgetDebt = clone $evalBudgetDebt;
            $newEvalBudgetDebt->setEvalBudgetPerson($this);
            $newEvalBudgetDebts->add($newEvalBudgetDebt);
        }

        $this->evalBudgetResources = $newEvalBudgetResources;
        $this->evalBudgetCharges = $newEvalBudgetCharges;
        $this->evalBudgetDebts = $newEvalBudgetDebts;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIncomeTax(): ?int
    {
        return $this->incomeTax;
    }

    public function getIncomeTaxToString(): ?string
    {
        return $this->incomeTax ? EvaluationChoices::YES_NO[$this->incomeTax] : null;
    }

    public function setIncomeTax(?int $incomeTax): self
    {
        $this->incomeTax = $incomeTax;

        return $this;
    }

    public function getIncomeN1Amt(): ?float
    {
        return $this->incomeN1Amt;
    }

    public function setIncomeN1Amt(?float $incomeN1Amt): self
    {
        $this->incomeN1Amt = $incomeN1Amt;

        return $this;
    }

    public function getIncomeN2Amt(): ?float
    {
        return $this->incomeN2Amt;
    }

    public function setIncomeN2Amt(?float $incomeN2Amt): self
    {
        $this->incomeN2Amt = $incomeN2Amt;

        return $this;
    }

    public function getResourcesComment(): ?string
    {
        return $this->resourcesComment;
    }

    public function setResourcesComment(?string $resourcesComment): self
    {
        $this->resourcesComment = $resourcesComment;

        return $this;
    }

    public function getCharge(): ?int
    {
        return $this->charge;
    }

    public function getChargeToString(): ?string
    {
        return $this->charge ? EvaluationChoices::YES_NO[$this->charge] : null;
    }

    public function setCharge(?int $charge): self
    {
        $this->charge = $charge;

        return $this;
    }

    public function getEvalBudgetChargesToArray(): array
    {
        if (!$this->evalBudgetCharges) {
            return [];
        }

        $evalBudgetCharges = [];

        foreach ($this->evalBudgetCharges as $evalBudgetCharge) {
            $evalBudgetCharges[] = EvalBudgetCharge::CHARGES[$evalBudgetCharge->getType()].
                (EvalBudgetCharge::OTHER === $evalBudgetCharge->getType() && $evalBudgetCharge->getComment() ? ' ('.$evalBudgetCharge->getComment().')' : '');
        }

        return $evalBudgetCharges;
    }

    public function getEvalBudgetChargesToString(): ?string
    {
        return join(', ', $this->getEvalBudgetChargesToArray());
    }

    public function getChargesAmt(): ?float
    {
        return $this->chargesAmt;
    }

    public function setChargesAmt(?float $chargesAmt): self
    {
        $this->chargesAmt = $chargesAmt;

        return $this;
    }

    public function getChargeComment(): ?string
    {
        return $this->chargeComment;
    }

    public function setChargeComment(?string $chargeComment): self
    {
        $this->chargeComment = $chargeComment;

        return $this;
    }

    public function getDebt(): ?int
    {
        return $this->debt;
    }

    public function getDebtToString(): ?string
    {
        return $this->debt ? EvaluationChoices::YES_NO[$this->debt] : null;
    }

    public function setDebt(?int $debt): self
    {
        $this->debt = $debt;

        return $this;
    }

    public function getEvalBudgetDebtsToArray(): array
    {
        if (!$this->evalBudgetDebts) {
            return [];
        }

        $evalBudgetDebts = [];

        foreach ($this->evalBudgetDebts as $evalBudgetDebt) {
            $evalBudgetDebts[] = EvalBudgetDebt::DEBTS[$evalBudgetDebt->getType()].
                (EvalBudgetDebt::OTHER === $evalBudgetDebt->getType() && $evalBudgetDebt->getComment()
                    ? ' ('.$evalBudgetDebt->getComment().')' : '');
        }

        return $evalBudgetDebts;
    }

    public function getEvalBudgetDebtsToString(): ?string
    {
        return join(', ', $this->getEvalBudgetDebtsToArray());
    }

    public function getDebtsAmt(): ?float
    {
        return $this->debtsAmt;
    }

    public function setDebtsAmt(?float $debtsAmt): self
    {
        $this->debtsAmt = $debtsAmt;

        return $this;
    }

    public function getDebtComment(): ?string
    {
        return $this->debtComment;
    }

    public function setDebtComment(?string $debtComment): self
    {
        $this->debtComment = $debtComment;

        return $this;
    }

    public function getOverIndebtRecord(): ?int
    {
        return $this->overIndebtRecord;
    }

    public function getOverIndebtRecordToString(): ?string
    {
        return $this->overIndebtRecord ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->overIndebtRecord] : null;
    }

    public function setOverIndebtRecord(?int $overIndebtRecord): self
    {
        $this->overIndebtRecord = $overIndebtRecord;

        return $this;
    }

    public function getOverIndebtRecordDate(): ?\DateTimeInterface
    {
        return $this->overIndebtRecordDate;
    }

    public function setOverIndebtRecordDate(?\DateTimeInterface $overIndebtRecordDate): self
    {
        $this->overIndebtRecordDate = $overIndebtRecordDate;

        return $this;
    }

    public function getSettlementPlan(): ?int
    {
        return $this->settlementPlan;
    }

    public function getSettlementPlanToString(): ?string
    {
        return $this->settlementPlan ? self::SETTLEMENT_PLAN[$this->settlementPlan] : null;
    }

    public function setSettlementPlan(?int $settlementPlan): self
    {
        $this->settlementPlan = $settlementPlan;

        return $this;
    }

    public function getMoratorium(): ?int
    {
        return $this->moratorium;
    }

    public function getMoratoriumToString(): ?string
    {
        return $this->moratorium ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->moratorium] : null;
    }

    public function setMoratorium(?int $moratorium): self
    {
        $this->moratorium = $moratorium;

        return $this;
    }

    public function getEndRightsDate(): ?\DateTimeInterface
    {
        return $this->endRightsDate;
    }

    public function setEndRightsDate(?\DateTimeInterface $endRightsDate): self
    {
        $this->endRightsDate = $endRightsDate;

        return $this;
    }

    public function getCommentEvalBudget(): ?string
    {
        return $this->commentEvalBudget;
    }

    public function setCommentEvalBudget(?string $commentEvalBudget): self
    {
        $this->commentEvalBudget = $commentEvalBudget;

        return $this;
    }

    public function getEvaluationPerson(): ?EvaluationPerson
    {
        return $this->evaluationPerson;
    }

    public function setEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        $this->evaluationPerson = $evaluationPerson;

        return $this;
    }

    /**
     * @return Collection<EvalBudgetResource>|null
     */
    public function getEvalBudgetResources(): ?Collection
    {
        return $this->evalBudgetResources;
    }

    public function addEvalBudgetResource(EvalBudgetResource $evalBudgetResource): self
    {
        if (!$this->evalBudgetResources->contains($evalBudgetResource)) {
            $this->evalBudgetResources[] = $evalBudgetResource;
            $evalBudgetResource->setEvalBudgetPerson($this);
        }

        return $this;
    }

    public function removeEvalBudgetResource(EvalBudgetResource $evalBudgetResource): self
    {
        if ($this->evalBudgetResources->removeElement($evalBudgetResource)) {
            // set the owning side to null (unless already changed)
            if ($evalBudgetResource->getEvalBudgetPerson() === $this) {
                $evalBudgetResource->setEvalBudgetPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<EvalBudgetCharge>|null
     */
    public function getEvalBudgetCharges(): ?Collection
    {
        return $this->evalBudgetCharges;
    }

    public function addEvalBudgetCharge(EvalBudgetCharge $evalBudgetCharge): self
    {
        if (!$this->evalBudgetCharges->contains($evalBudgetCharge)) {
            $this->evalBudgetCharges[] = $evalBudgetCharge;
            $evalBudgetCharge->setEvalBudgetPerson($this);
        }

        return $this;
    }

    public function removeEvalBudgetCharge(EvalBudgetCharge $evalBudgetCharge): self
    {
        if ($this->evalBudgetCharges->removeElement($evalBudgetCharge)) {
            // set the owning side to null (unless already changed)
            if ($evalBudgetCharge->getEvalBudgetPerson() === $this) {
                $evalBudgetCharge->setEvalBudgetPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<EvalBudgetDebt>|null
     */
    public function getEvalBudgetDebts(): ?Collection
    {
        return $this->evalBudgetDebts;
    }

    public function addEvalBudgetDebt(EvalBudgetDebt $evalBudgetDebt): self
    {
        if (!$this->evalBudgetDebts->contains($evalBudgetDebt)) {
            $this->evalBudgetDebts[] = $evalBudgetDebt;
            $evalBudgetDebt->setEvalBudgetPerson($this);
        }

        return $this;
    }

    public function removeEvalBudgetDebt(EvalBudgetDebt $evalBudgetDebt): self
    {
        if ($this->evalBudgetDebts->removeElement($evalBudgetDebt)) {
            // set the owning side to null (unless already changed)
            if ($evalBudgetDebt->getEvalBudgetPerson() === $this) {
                $evalBudgetDebt->setEvalBudgetPerson(null);
            }
        }

        return $this;
    }
}
