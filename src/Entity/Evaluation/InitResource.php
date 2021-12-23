<?php

namespace App\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResourceRepository::class)
 */
class InitResource extends AbstractFinance
{
    /**
     * @ORM\ManyToOne(targetEntity=InitEvalPerson::class, inversedBy="resources")
     * @ORM\JoinColumn(nullable=false)
     */
    private $initEvalPerson;

    public function getTypeToString(): ?string
    {
        return $this->type ? Resource::RESOURCES[$this->type] : null;
    }

    public function getInitEvalPerson(): ?InitEvalPerson
    {
        return $this->initEvalPerson;
    }

    public function setInitEvalPerson(?InitEvalPerson $initEvalPerson): self
    {
        $this->initEvalPerson = $initEvalPerson;

        return $this;
    }
}
