<?php

namespace App\Entity\Evaluation;

trait ResourcesEntityTrait
{
    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $resource;

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

    public function getResource(): ?int
    {
        return $this->resource;
    }

    public function getResourceToString(): ?string
    {
        return $this->resource ? EvalBudgetPerson::RESOURCES[$this->resource] : null;
    }

    public function setResource(?int $resource): self
    {
        $this->resource = $resource;

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

    public function getSalariesAmt(): ?float
    {
        return $this->getEvalBudgetResourceAmt([Resource::SALARY]);
    }

    public function getAreAmt(): ?float
    {
        return $this->getEvalBudgetResourceAmt([Resource::ARE]);
    }

    public function getRsaAmt(): ?float
    {
        return $this->getEvalBudgetResourceAmt([Resource::RSA]);
    }

    public function getIjAmt(): ?float
    {
        return $this->getEvalBudgetResourceAmt([Resource::IJ]);
    }

    public function getAfAmt(): ?float
    {
        return $this->getEvalBudgetResourceAmt([Resource::AF]);
    }

    public function getEvalBudgetResourcesToArray(): array
    {
        if (!$this->evalBudgetResources) {
            return [];
        }

        $resources = [];

        foreach ($this->evalBudgetResources as $evalBudgetResources) {
            $resources[] = Resource::SHORT_RESOURCES[$evalBudgetResources->getType()].
                (Resource::OTHER === $evalBudgetResources->getType() && $evalBudgetResources->getComment()
                ? ' ('.$evalBudgetResources->getComment().')' : '');
        }

        return $resources;
    }

    public function getEvalBudgetResourcesToString(): ?string
    {
        return join(', ', $this->getEvalBudgetResourcesToArray());
    }

    protected function getEvalBudgetResourceAmt(array $resourceTypes): float
    {
        $amount = 0;

        foreach ($this->evalBudgetResources as $evalBudgetResource) {
            if (in_array($evalBudgetResource->getType(), $resourceTypes)) {
                $amount += $evalBudgetResource->getAmount();
            }
        }

        return $amount;
    }
}
