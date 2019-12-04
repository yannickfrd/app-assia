<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SitBudgetGroupRepository")
 */
class SitBudgetGroup
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
    private $ressourcesGroupAmt;

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
    private $commentSitBudget;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGroup", inversedBy="sitBudgetGroup", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRessourcesGroupAmt(): ?int
    {
        return $this->ressourcesGroupAmt;
    }

    public function setRessourcesGroupAmt(?int $ressourcesGroupAmt): self
    {
        $this->ressourcesGroupAmt = $ressourcesGroupAmt;

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

    public function getCommentSitBudget(): ?string
    {
        return $this->commentSitBudget;
    }

    public function setCommentSitBudget(?string $commentSitBudget): self
    {
        $this->commentSitBudget = $commentSitBudget;

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
