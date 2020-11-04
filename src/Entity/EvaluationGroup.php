<?php

namespace App\Entity;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvaluationGroupRepository")
 */
class EvaluationGroup
{
    use CreatedUpdatedEntityTrait;

    public const CACHE_EVALUATION_KEY = 'evaluation.support_group';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $backgroundPeople;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportGroup", inversedBy="evaluationsGroup")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationPerson", mappedBy="evaluationGroup", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $evaluationPeople;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvalSocialGroup", mappedBy="evaluationGroup", cascade={"persist", "remove"})
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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\InitEvalGroup", cascade={"persist", "remove"})
     */
    private $initEvalGroup;

    /**
     * @ORM\OneToOne(targetEntity=EvalHotelLifeGroup::class, mappedBy="evaluationGroup", cascade={"persist", "remove"})
     */
    private $evalHotelLifeGroup;

    public function __construct()
    {
        $this->evaluationPeople = new ArrayCollection();
    }

    public function __clone()
    {
        $now = new \DateTime();
        $this->setCreatedAt($now);
        $this->setUpdatedAt($now);

        $newEvaluationPeople = new ArrayCollection();

        foreach ($this->evaluationPeople as $evaluationPerson) {
            $newEvaluationPerson = clone $evaluationPerson;
            $newEvaluationPerson->setEvaluationGroup($this);
            $newEvaluationPeople->add($newEvaluationPerson);
        }
        $this->evaluationPeople = $newEvaluationPeople;

        if ($this->evalBudgetGroup) {
            $this->setEvalBudgetGroup(clone $this->evalBudgetGroup);
        }
        if ($this->evalFamilyGroup) {
            $this->setEvalFamilyGroup(clone $this->evalFamilyGroup);
        }
        if ($this->evalHousingGroup) {
            $this->setEvalHousingGroup(clone $this->evalHousingGroup);
        }
        if ($this->evalSocialGroup) {
            $this->setEvalSocialGroup(clone $this->evalSocialGroup);
        }
        $this->setInitEvalGroup(new InitEvalGroup());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
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

    public function getBackgroundPeople(): ?string
    {
        return $this->backgroundPeople;
    }

    public function setBackgroundPeople(?string $backgroundPeople): self
    {
        $this->backgroundPeople = $backgroundPeople;

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
        if ($evalSocialGroup->getId() || false == $this->objectIsEmpty($evalSocialGroup)) {
            $this->evalSocialGroup = $evalSocialGroup;
        }

        // set the owning side of the relation if necessary
        if ($this !== $evalSocialGroup->getEvaluationGroup()) {
            $evalSocialGroup->setEvaluationGroup($this);
        }

        return $this;
    }

    public function getEvalFamilyGroup(): ?EvalFamilyGroup
    {
        return $this->evalFamilyGroup;
    }

    public function setEvalFamilyGroup(EvalFamilyGroup $evalFamilyGroup): self
    {
        if ($evalFamilyGroup->getId() || false == $this->objectIsEmpty($evalFamilyGroup)) {
            $this->evalFamilyGroup = $evalFamilyGroup;
        }
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
        if ($evalHousingGroup->getId() || false == $this->objectIsEmpty($evalHousingGroup)) {
            $this->evalHousingGroup = $evalHousingGroup;
        }
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
    public function getEvaluationPeople(): ?Collection
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

    public function getInitEvalGroup(): ?InitEvalGroup
    {
        return $this->initEvalGroup;
    }

    public function setInitEvalGroup(?InitEvalGroup $initEvalGroup): self
    {
        $this->initEvalGroup = $initEvalGroup;

        return $this;
    }

    public function getEvalHotelLifeGroup(): ?EvalHotelLifeGroup
    {
        return $this->evalHotelLifeGroup;
    }

    public function setEvalHotelLifeGroup(EvalHotelLifeGroup $evalHotelLifeGroup): self
    {
        if ($evalHotelLifeGroup->getId() || false == $this->objectIsEmpty($evalHotelLifeGroup)) {
            $this->evalHotelLifeGroup = $evalHotelLifeGroup;
        }
        // set the owning side of the relation if necessary
        if ($this !== $evalHotelLifeGroup->getEvaluationGroup()) {
            $evalHotelLifeGroup->setEvaluationGroup($this);
        }

        return $this;
    }

    protected function objectIsEmpty(object $originRequest)
    {
        foreach ((array) $originRequest as $value) {
            if ($value) {
                return false;
            }
        }

        return true;
    }
}
