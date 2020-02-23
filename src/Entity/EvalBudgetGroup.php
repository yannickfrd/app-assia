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
     * @ORM\Column(type="integer", nullable=true)
     */
    private $taxIncomeN1Amt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $taxIncomeN2Amt;

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

    public function getTaxIncomeN1Amt(): ?int
    {
        return $this->taxIncomeN1Amt;
    }

    public function setTaxIncomeN1Amt(?int $taxIncomeN1Amt): self
    {
        $this->taxIncomeN1Amt = $taxIncomeN1Amt;

        return $this;
    }

    public function getTaxIncomeN2Amt(): ?int
    {
        return $this->taxIncomeN2Amt;
    }

    public function setTaxIncomeN2Amt(?int $taxIncomeN2Amt): self
    {
        $this->taxIncomeN2Amt = $taxIncomeN2Amt;

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
