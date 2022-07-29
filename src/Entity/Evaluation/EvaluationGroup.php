<?php

namespace App\Entity\Evaluation;

use App\Entity\Support\SupportGroup;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvaluationGroupRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 * @ORM\HasLifecycleCallbacks
 */
class EvaluationGroup
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

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
     * @ORM\Column(type="text", nullable=true)
     */
    private $conclusion;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportGroup", inversedBy="evaluationsGroup")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\OneToMany(targetEntity=EvaluationPerson::class, mappedBy="evaluationGroup", cascade={"persist", "remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     */
    private $evaluationPeople;

    /**
     * @ORM\OneToOne(targetEntity=EvalSocialGroup::class, inversedBy="evaluationGroup", cascade={"persist", "remove"})
     */
    private $evalSocialGroup;

    /**
     * @ORM\OneToOne(targetEntity=EvalFamilyGroup::class, inversedBy="evaluationGroup", cascade={"persist", "remove"})
     */
    private $evalFamilyGroup;

    /**
     * @ORM\OneToOne(targetEntity=EvalHousingGroup::class, inversedBy="evaluationGroup", cascade={"persist", "remove"})
     */
    private $evalHousingGroup;

    /**
     * @ORM\OneToOne(targetEntity=EvalBudgetGroup::class, inversedBy="evaluationGroup", cascade={"persist", "remove"})
     */
    private $evalBudgetGroup;

    /**
     * @ORM\OneToOne(targetEntity=EvalHotelLifeGroup::class, inversedBy="evaluationGroup", cascade={"persist", "remove"})
     */
    private $evalHotelLifeGroup;

    public function __construct()
    {
        $this->evaluationPeople = new ArrayCollection();
    }

    public function __clone()
    {
        $now = new \DateTime();

        $this->setCreatedAt($now)
            ->setUpdatedAt($now)
        ;

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

        $this->setEvalInitGroup(new EvalInitGroup());
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

    public function getConclusion(): ?string
    {
        return $this->conclusion;
    }

    public function setConclusion(?string $conclusion): self
    {
        $this->conclusion = $conclusion;

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
        if ($evalSocialGroup->getId() || true === (bool) array_filter((array) $evalSocialGroup)) {
            $this->evalSocialGroup = $evalSocialGroup;
        }

        return $this;
    }

    public function getEvalFamilyGroup(): ?EvalFamilyGroup
    {
        return $this->evalFamilyGroup;
    }

    public function setEvalFamilyGroup(EvalFamilyGroup $evalFamilyGroup): self
    {
        if ($evalFamilyGroup->getId() || true === (bool) array_filter((array) $evalFamilyGroup)) {
            $this->evalFamilyGroup = $evalFamilyGroup;
        }

        return $this;
    }

    public function getEvalHousingGroup(): ?EvalHousingGroup
    {
        return $this->evalHousingGroup;
    }

    public function setEvalHousingGroup(EvalHousingGroup $evalHousingGroup): self
    {
        if ($evalHousingGroup->getId() || true === (bool) array_filter((array) $evalHousingGroup)) {
            $this->evalHousingGroup = $evalHousingGroup;
        }

        return $this;
    }

    public function getEvalBudgetGroup(): ?EvalBudgetGroup
    {
        return $this->evalBudgetGroup;
    }

    public function setEvalBudgetGroup(EvalBudgetGroup $evalBudgetGroup): self
    {
        if ($evalBudgetGroup->getId() || true === (bool) array_filter((array) $evalBudgetGroup)) {
            $this->evalBudgetGroup = $evalBudgetGroup;
        }

        return $this;
    }

    /**
     * @return Collection<EvaluationPerson>|null
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
        }

        return $this;
    }

    public function getEvalInitGroup(): ?EvalInitGroup
    {
        return $this->supportGroup->getEvalInitGroup();
    }

    public function setEvalInitGroup(?EvalInitGroup $evalInitGroup): self
    {
        $this->supportGroup->setEvalInitGroup($evalInitGroup);

        return $this;
    }

    public function getEvalHotelLifeGroup(): ?EvalHotelLifeGroup
    {
        return $this->evalHotelLifeGroup;
    }

    public function setEvalHotelLifeGroup(EvalHotelLifeGroup $evalHotelLifeGroup): self
    {
        if ($evalHotelLifeGroup->getId() || true === (bool) array_filter((array) $evalHotelLifeGroup)) {
            $this->evalHotelLifeGroup = $evalHotelLifeGroup;
        }

        return $this;
    }
}
