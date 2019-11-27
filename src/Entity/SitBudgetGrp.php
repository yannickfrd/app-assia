<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SitBudgetGrpRepository")
 */
class SitBudgetGrp
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
    private $ressourcesGrpAmt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $chargesGrpAmt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $debtsGrpAmt;

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
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGrp", inversedBy="sitBudgetGrp", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGrp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRessourcesGrpAmt(): ?int
    {
        return $this->ressourcesGrpAmt;
    }

    public function setRessourcesGrpAmt(?int $ressourcesGrpAmt): self
    {
        $this->ressourcesGrpAmt = $ressourcesGrpAmt;

        return $this;
    }

    public function getChargesGrpAmt(): ?int
    {
        return $this->chargesGrpAmt;
    }

    public function setChargesGrpAmt(?int $chargesGrpAmt): self
    {
        $this->chargesGrpAmt = $chargesGrpAmt;

        return $this;
    }

    public function getDebtsGrpAmt(): ?int
    {
        return $this->debtsGrpAmt;
    }

    public function setDebtsGrpAmt(?int $debtsGrpAmt): self
    {
        $this->debtsGrpAmt = $debtsGrpAmt;

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

    public function getSupportGrp(): ?SupportGrp
    {
        return $this->supportGrp;
    }

    public function setSupportGrp(SupportGrp $supportGrp): self
    {
        $this->supportGrp = $supportGrp;

        return $this;
    }
}
