<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalBudgetGroupRepository")
 */
class EvalBudgetGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $resourcesGroupAmt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $chargesGroupAmt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $debtsGroupAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $incomeN1Amt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $incomeN2Amt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $monthlyRepaymentAmt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $budgetBalanceAmt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalBudget;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvaluationGroup", inversedBy="evalBudgetGroup", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $evaluationGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResourcesGroupAmt(): ?int
    {
        return $this->resourcesGroupAmt;
    }

    public function setResourcesGroupAmt(?int $resourcesGroupAmt): self
    {
        $this->resourcesGroupAmt = $resourcesGroupAmt;

        return $this;
    }

    public function getChargesGroupAmt(): ?int
    {
        return $this->chargesGroupAmt;
    }

    public function setChargesGroupAmt(?int $chargesGroupAmt): self
    {
        $this->chargesGroupAmt = $chargesGroupAmt;

        return $this;
    }

    public function getDebtsGroupAmt(): ?int
    {
        return $this->debtsGroupAmt;
    }

    public function setDebtsGroupAmt(?int $debtsGroupAmt): self
    {
        $this->debtsGroupAmt = $debtsGroupAmt;

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

    public function getMonthlyRepaymentAmt(): ?float
    {
        return $this->monthlyRepaymentAmt;
    }

    public function setMonthlyRepaymentAmt(?float $monthlyRepaymentAmt): self
    {
        $this->monthlyRepaymentAmt = $monthlyRepaymentAmt;

        return $this;
    }

    public function getBudgetBalanceAmt(): ?int
    {
        return $this->budgetBalanceAmt;
    }

    public function setBudgetBalanceAmt(?int $budgetBalanceAmt): self
    {
        $this->budgetBalanceAmt = $budgetBalanceAmt;

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
