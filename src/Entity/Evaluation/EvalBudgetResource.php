<?php

namespace App\Entity\Evaluation;

use App\Repository\Evaluation\EvalBudgetResourceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EvalBudgetResourceRepository::class)
 */
class EvalBudgetResource extends AbstractFinance
{
    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\ManyToOne(targetEntity=EvalBudgetPerson::class, inversedBy="evalBudgetResources")
     */
    private $evalBudgetPerson;

    /**
     * @ORM\ManyToOne(targetEntity=Resource::class)
     */
    private $resource;

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getTypeToString(): ?string
    {
        return $this->type ? Resource::RESOURCES[$this->type] : null;
    }

    public function getEvalBudgetPerson(): ?EvalBudgetPerson
    {
        return $this->evalBudgetPerson;
    }

    public function setEvalBudgetPerson(?EvalBudgetPerson $evalBudgetPerson): self
    {
        $this->evalBudgetPerson = $evalBudgetPerson;

        return $this;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): self
    {
        $this->resource = $resource;

        return $this;
    }
}
