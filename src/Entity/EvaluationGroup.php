<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluationGroupRepository")
 */
class EvaluationGroup
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportGroup", inversedBy="evaluationsGroup")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationPerson", mappedBy="evaluationGroup", orphanRemoval=true)
     */
    private $evaluationPeople;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvalSocialGroup", mappedBy="evaluationGroup", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalSocialGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvalFamilyGroup", mappedBy="evaluationGroup", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalFamilyGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvalHousingGroup", mappedBy="evaluationGroup", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalHousingGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvalBudgetGroup", mappedBy="evaluationGroup", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalBudgetGroup;


    public function __construct()
    {
        $this->evaluationPeople = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }


    public function getEvalSocialGroup(): ?EvalSocialGroup
    {
        return $this->evalSocialGroup;
    }

    public function setEvalSocialGroup(EvalSocialGroup $evalSocialGroup): self
    {
        $this->evalSocialGroup = $evalSocialGroup;

        // set the owning side of the relation if necessary
        if ($this !== $evalSocialGroup->getEvaluationGroup()) {
            $evalSocialGroup->setEvaluationGroup($this);
        }

        return $this;
    }

    public function getevalFamilyGroup(): ?evalFamilyGroup
    {
        return $this->evalFamilyGroup;
    }

    public function setevalFamilyGroup(evalFamilyGroup $evalFamilyGroup): self
    {
        $this->evalFamilyGroup = $evalFamilyGroup;

        // set the owning side of the relation if necessary
        if ($this !== $evalFamilyGroup->getEvaluationGroup()) {
            $evalFamilyGroup->setEvaluationGroup($this);
        }

        return $this;
    }

    public function getEvalHousingGroup(): ?EvalHousingGroup
    {
        return $this->evalHousingGroup;
    }

    public function setEvalHousingGroup(EvalHousingGroup $evalHousingGroup): self
    {
        $this->evalHousingGroup = $evalHousingGroup;

        // set the owning side of the relation if necessary
        if ($this !== $evalHousingGroup->getEvaluationGroup()) {
            $evalHousingGroup->setEvaluationGroup($this);
        }

        return $this;
    }

    public function getEvalBudgetGroup(): ?EvalBudgetGroup
    {
        return $this->evalBudgetGroup;
    }

    public function setEvalBudgetGroup(EvalBudgetGroup $evalBudgetGroup): self
    {
        $this->evalBudgetGroup = $evalBudgetGroup;

        // set the owning side of the relation if necessary
        if ($this !== $evalBudgetGroup->getEvaluationGroup()) {
            $evalBudgetGroup->setEvaluationGroup($this);
        }

        return $this;
    }

    /**
     * @return Collection|EvaluationPerson[]
     */
    public function getEvaluationPeople(): Collection
    {
        return $this->evaluationPeople;
    }

    public function addEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        if (!$this->evaluationPeople->contains($evaluationPerson)) {
            $this->evaluationPeople[] = $evaluationPerson;
            $evaluationPerson->setEvaluationGroup($this);
        }

        return $this;
    }

    public function removeEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        if ($this->evaluationPeople->contains($evaluationPerson)) {
            $this->evaluationPeople->removeElement($evaluationPerson);
            // set the owning side to null (unless already changed)
            if ($evaluationPerson->getEvaluationGroup() === $this) {
                $evaluationPerson->setEvaluationGroup(null);
            }
        }

        return $this;
    }
}
