<?php

namespace App\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResourceRepository::class)
 */
class EvalInitResource extends AbstractFinance
{
    /**
     * @ORM\ManyToOne(targetEntity=EvalInitPerson::class, inversedBy="evalBudgetResources")
     * @ORM\JoinColumn(nullable=false)
     */
    private $evalInitPerson;

    public function getTypeToString(): ?string
    {
        return $this->type ? Resource::RESOURCES[$this->type] : null;
    }

    public function getEvalInitPerson(): ?EvalInitPerson
    {
        return $this->evalInitPerson;
    }

    public function setEvalInitPerson(?EvalInitPerson $evalInitPerson): self
    {
        $this->evalInitPerson = $evalInitPerson;

        return $this;
    }
}
