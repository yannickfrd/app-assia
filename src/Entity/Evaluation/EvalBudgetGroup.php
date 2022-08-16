<?php

namespace App\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalBudgetGroupRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalBudgetGroup
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"export", "exportable"})
     */
    private $resourcesGroupAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"export", "exportable"})
     */
    private $chargesGroupAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"export", "exportable"})
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
     * @Groups({"export", "exportable"})
     */
    private $budgetBalanceAmt;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"export", "exportable"})
     */
    private $contributionAmt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cafId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cafAttachment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalBudget;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvaluationGroup", inversedBy="evalBudgetGroup", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $evaluationGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getResourcesGroupAmt(): ?float
    {
        return $this->resourcesGroupAmt;
    }

    public function setResourcesGroupAmt(?float $resourcesGroupAmt): self
    {
        $this->resourcesGroupAmt = $resourcesGroupAmt;

        return $this;
    }

    public function getChargesGroupAmt(): ?float
    {
        return $this->chargesGroupAmt;
    }

    public function setChargesGroupAmt(?float $chargesGroupAmt): self
    {
        $this->chargesGroupAmt = $chargesGroupAmt;

        return $this;
    }

    public function getDebtsGroupAmt(): ?float
    {
        return $this->debtsGroupAmt;
    }

    public function setDebtsGroupAmt(?float $debtsGroupAmt): self
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

    public function getBudgetBalanceAmt(): ?float
    {
        return $this->budgetBalanceAmt;
    }

    public function setBudgetBalanceAmt(?float $budgetBalanceAmt): self
    {
        $this->budgetBalanceAmt = $budgetBalanceAmt;

        return $this;
    }

    public function getContributionAmt(): ?float
    {
        return $this->contributionAmt;
    }

    public function setContributionAmt(?float $contributionAmt): self
    {
        $this->contributionAmt = $contributionAmt;

        return $this;
    }

    public function getCafId(): ?string
    {
        return $this->cafId;
    }

    public function setCafId(?string $cafId): self
    {
        $this->cafId = $cafId;

        return $this;
    }

    public function getCafAttachment(): ?string
    {
        return $this->cafAttachment;
    }

    public function setCafAttachment(?string $cafAttachment): self
    {
        $this->cafAttachment = $cafAttachment;

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
