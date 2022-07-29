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
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Evaluation\EvaluationGroup", inversedBy="evaluationPeople")
     */
    private $evaluationGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportPerson", inversedBy="evaluations")
     */
    private $supportPerson;

    /**
     * @ORM\OneToOne(targetEntity=EvalAdmPerson::class, inversedBy="evaluationPerson", cascade={"persist", "remove"})
     */
    private $evalAdmPerson;

    /**
     * @ORM\OneToOne(targetEntity=EvalBudgetPerson::class, inversedBy="evaluationPerson", cascade={"persist", "remove"})
     */
    private $evalBudgetPerson;

    /**
     * @ORM\OneToOne(targetEntity=EvalFamilyPerson::class, inversedBy="evaluationPerson", cascade={"persist", "remove"})
     */
    private $evalFamilyPerson;

    /**
     * @ORM\OneToOne(targetEntity=EvalProfPerson::class, inversedBy="evaluationPerson", cascade={"persist", "remove"})
     */
    private $evalProfPerson;

    /**
     * @ORM\OneToOne(targetEntity=EvalSocialPerson::class, inversedBy="evaluationPerson", cascade={"persist", "remove"})
     */
    private $evalSocialPerson;

    /**
     * @ORM\OneToOne(targetEntity=EvalJusticePerson::class, inversedBy="evaluationPerson", cascade={"persist", "remove"})
     */
    private $evalJusticePerson;

    public function __clone()
    {
        $now = new \DateTime();

        $this->setCreatedAt($now)
            ->setUpdatedAt($now)
        ;

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

        $this->setEvalInitPerson(new EvalInitPerson());
    }

    public function getId(): ?int
    {
        return $this->id;
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
        if ($evalFamilyPerson->getId() || true === (bool) array_filter((array) $evalFamilyPerson)) {
            $this->evalFamilyPerson = $evalFamilyPerson;
        }

        return $this;
    }

    public function getEvalProfPerson(): ?EvalProfPerson
    {
        return $this->evalProfPerson;
    }

    public function setEvalProfPerson(EvalProfPerson $evalProfPerson): self
    {
        if ($evalProfPerson->getId() || true === (bool) array_filter((array) $evalProfPerson)) {
            $this->evalProfPerson = $evalProfPerson;
        }

        return $this;
    }

    public function getEvalAdmPerson(): ?EvalAdmPerson
    {
        return $this->evalAdmPerson;
    }

    public function setEvalAdmPerson(EvalAdmPerson $evalAdmPerson): self
    {
        if ($evalAdmPerson->getId() || true === (bool) array_filter((array) $evalAdmPerson)) {
            $this->evalAdmPerson = $evalAdmPerson;
        }

        return $this;
    }

    public function getEvalBudgetPerson(): ?EvalBudgetPerson
    {
        return $this->evalBudgetPerson;
    }

    public function setEvalBudgetPerson(EvalBudgetPerson $evalBudgetPerson): self
    {
        if ($evalBudgetPerson->getId() || true === (bool) array_filter((array) $evalBudgetPerson)) {
            $this->evalBudgetPerson = $evalBudgetPerson;
        }

        return $this;
    }

    public function getEvalSocialPerson(): ?EvalSocialPerson
    {
        return $this->evalSocialPerson;
    }

    public function setEvalSocialPerson(EvalSocialPerson $evalSocialPerson): self
    {
        if ($evalSocialPerson->getId() || true === (bool) array_filter((array) $evalSocialPerson)) {
            $this->evalSocialPerson = $evalSocialPerson;
        }

        return $this;
    }

    public function getEvalJusticePerson(): ?EvalJusticePerson
    {
        return $this->evalJusticePerson;
    }

    public function setEvalJusticePerson(EvalJusticePerson $evalJusticePerson): self
    {
        if ($evalJusticePerson->getId() || true === (bool) array_filter((array) $evalJusticePerson)) {
            $this->evalJusticePerson = $evalJusticePerson;
        }

        return $this;
    }

    public function getEvalInitPerson(): ?EvalInitPerson
    {
        return $this->supportPerson->getEvalInitPerson();
    }

    public function setEvalInitPerson(?EvalInitPerson $evalInitPerson): self
    {
        $this->supportPerson->setEvalInitPerson($evalInitPerson);

        return $this;
    }
}
