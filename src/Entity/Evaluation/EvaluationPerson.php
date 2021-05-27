<?php

namespace App\Entity\Evaluation;

use App\Entity\Support\SupportPerson;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvaluationPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvaluationPerson
{
    use CreatedUpdatedEntityTrait;  // A supprimer après test
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluation\EvaluationGroup", inversedBy="evaluationPeople")
     * @ORM\JoinColumn(nullable=true)
     */
    private $evaluationGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportPerson", inversedBy="evaluationsPerson")
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvalAdmPerson", mappedBy="evaluationPerson", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalAdmPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvalBudgetPerson", mappedBy="evaluationPerson", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalBudgetPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvalFamilyPerson", mappedBy="evaluationPerson", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalFamilyPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvalProfPerson", mappedBy="evaluationPerson", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalProfPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvalSocialPerson", mappedBy="evaluationPerson", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalSocialPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvalJusticePerson", mappedBy="evaluationPerson", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalJusticePerson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluation\InitEvalPerson", cascade={"persist", "remove"})
     */
    private $initEvalPerson;

    public function __clone()
    {
        if ($this->evalAdmPerson) {
            $this->setEvalAdmPerson(clone $this->evalAdmPerson);
        }
        if ($this->evalBudgetPerson) {
            $this->setEvalBudgetPerson(clone $this->evalBudgetPerson);
        }
        if ($this->evalFamilyPerson) {
            $this->setEvalFamilyPerson(clone $this->evalFamilyPerson);
        }
        if ($this->evalJusticePerson) {
            $this->setEvalJusticePerson(clone $this->evalJusticePerson);
        }
        if ($this->evalProfPerson) {
            $this->setEvalProfPerson(clone $this->evalProfPerson);
        }
        if ($this->evalSocialPerson) {
            $this->setEvalSocialPerson(clone $this->evalSocialPerson);
        }

        $this->setInitEvalPerson(new InitEvalPerson());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvaluationGroup(): ?EvaluationGroup
    {
        return $this->evaluationGroup;
    }

    public function setEvaluationGroup(?EvaluationGroup $evaluationGroup): self
    {
        $this->evaluationGroup = $evaluationGroup;

        return $this;
    }

    public function getSupportPerson(): ?SupportPerson
    {
        return $this->supportPerson;
    }

    public function setSupportPerson(?SupportPerson $supportPerson): self
    {
        $this->supportPerson = $supportPerson;

        return $this;
    }

    public function getEvalFamilyPerson(): ?EvalFamilyPerson
    {
        return $this->evalFamilyPerson;
    }

    public function setEvalFamilyPerson(EvalFamilyPerson $evalFamilyPerson): self
    {
        $this->evalFamilyPerson = $evalFamilyPerson;

        // set the owning side of the relation if necessary
        if ($this !== $evalFamilyPerson->getEvaluationPerson()) {
            $evalFamilyPerson->setEvaluationPerson($this);
        }

        return $this;
    }

    public function getEvalProfPerson(): ?EvalProfPerson
    {
        return $this->evalProfPerson;
    }

    public function setEvalProfPerson(EvalProfPerson $evalProfPerson): self
    {
        $this->evalProfPerson = $evalProfPerson;

        // set the owning side of the relation if necessary
        if ($this !== $evalProfPerson->getEvaluationPerson()) {
            $evalProfPerson->setEvaluationPerson($this);
        }

        return $this;
    }

    public function getEvalAdmPerson(): ?EvalAdmPerson
    {
        return $this->evalAdmPerson;
    }

    public function setEvalAdmPerson(EvalAdmPerson $evalAdmPerson): self
    {
        $this->evalAdmPerson = $evalAdmPerson;

        // set the owning side of the relation if necessary
        if ($this !== $evalAdmPerson->getEvaluationPerson()) {
            $evalAdmPerson->setEvaluationPerson($this);
        }

        return $this;
    }

    public function getEvalBudgetPerson(): ?EvalBudgetPerson
    {
        return $this->evalBudgetPerson;
    }

    public function setEvalBudgetPerson(EvalBudgetPerson $evalBudgetPerson): self
    {
        $this->evalBudgetPerson = $evalBudgetPerson;

        // set the owning side of the relation if necessary
        if ($this !== $evalBudgetPerson->getEvaluationPerson()) {
            $evalBudgetPerson->setEvaluationPerson($this);
        }

        return $this;
    }

    public function getEvalSocialPerson(): ?EvalSocialPerson
    {
        return $this->evalSocialPerson;
    }

    public function setEvalSocialPerson(EvalSocialPerson $evalSocialPerson): self
    {
        $this->evalSocialPerson = $evalSocialPerson;

        // set the owning side of the relation if necessary
        if ($this !== $evalSocialPerson->getEvaluationPerson()) {
            $evalSocialPerson->setEvaluationPerson($this);
        }

        return $this;
    }

    public function getEvalJusticePerson(): ?EvalJusticePerson
    {
        return $this->evalJusticePerson;
    }

    public function setEvalJusticePerson(EvalJusticePerson $evalJusticePerson): self
    {
        $this->evalJusticePerson = $evalJusticePerson;

        // set the owning side of the relation if necessary
        if ($evalJusticePerson->getEvaluationPerson() !== $this) {
            $evalJusticePerson->setEvaluationPerson($this);
        }

        return $this;
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
