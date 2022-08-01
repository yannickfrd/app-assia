<?php

namespace App\Entity\Evaluation;

use App\Entity\Support\SupportPerson;
use App\Form\Utils\EvaluationChoices;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalInitPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalInitPerson
{
    use ResourcesEntityTrait;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paper;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paperType;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $rightSocialSecurity;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialSecurity;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $familyBreakdown;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $friendshipBreakdown;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $debt;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $debtsAmt;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $profStatus;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $contractType;

    /**
     * @ORM\OneToOne(targetEntity=SupportPerson::class, mappedBy="evalInitPerson")
     */
    private $supportPerson;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @var Collection<EvalInitResource>|null
     * @ORM\OneToMany(targetEntity=EvalInitResource::class, mappedBy="evalInitPerson", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"amount": "DESC"})
     */
    private $evalBudgetResources;

    public function __construct()
    {
        $this->evalBudgetResources = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaper(): ?int
    {
        return $this->paper;
    }

    /**
     * @Groups("export")
     */
    public function getPaperToString(): ?string
    {
        return $this->paper ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->paper] : null;
    }

    public function setPaper(?int $paper): self
    {
        $this->paper = $paper;

        return $this;
    }

    public function getPaperType(): ?int
    {
        return $this->paperType;
    }

    /**
     * @Groups("export")
     */
    public function getPaperTypeToString(): ?string
    {
        return $this->paperType ? EvalAdmPerson::PAPER_TYPE[$this->paperType] : null;
    }

    public function setPaperType(?int $paperType): self
    {
        $this->paperType = $paperType;

        return $this;
    }

    public function getRightSocialSecurity(): ?int
    {
        return $this->rightSocialSecurity;
    }

    /**
     * @Groups("export")
     */
    public function getRightSocialSecurityToString(): ?string
    {
        return $this->rightSocialSecurity ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->rightSocialSecurity] : null;
    }

    public function setRightSocialSecurity(?int $rightSocialSecurity): self
    {
        $this->rightSocialSecurity = $rightSocialSecurity;

        return $this;
    }

    public function getSocialSecurity(): ?int
    {
        return $this->socialSecurity;
    }

    /**
     * @Groups("export")
     */
    public function getSocialSecurityToString(): ?string
    {
        return $this->socialSecurity ? EvalSocialPerson::SOCIAL_SECURITY[$this->socialSecurity] : null;
    }

    public function setSocialSecurity(?int $socialSecurity): self
    {
        $this->socialSecurity = $socialSecurity;

        return $this;
    }

    public function getFamilyBreakdown(): ?int
    {
        return $this->familyBreakdown;
    }

    /**
     * @Groups("export")
     */
    public function getFamilyBreakdownToString(): ?string
    {
        return $this->familyBreakdown ? EvaluationChoices::YES_NO_PARTIAL[$this->familyBreakdown] : null;
    }

    public function setFamilyBreakdown(?int $familyBreakdown): self
    {
        $this->familyBreakdown = $familyBreakdown;

        return $this;
    }

    public function getFriendshipBreakdown(): ?int
    {
        return $this->friendshipBreakdown;
    }

    /**
     * @Groups("export")
     */
    public function getFriendshipBreakdownToString(): ?string
    {
        return $this->friendshipBreakdown ? EvaluationChoices::YES_NO_PARTIAL[$this->friendshipBreakdown] : null;
    }

    public function setFriendshipBreakdown(?int $friendshipBreakdown): self
    {
        $this->friendshipBreakdown = $friendshipBreakdown;

        return $this;
    }

    public function getProfStatus(): ?int
    {
        return $this->profStatus;
    }

    /**
     * @Groups("export")
     */
    public function getProfStatusToString(): ?string
    {
        return $this->profStatus ? EvalProfPerson::PROF_STATUS[$this->profStatus] : null;
    }

    public function setProfStatus(?int $profStatus): self
    {
        $this->profStatus = $profStatus;

        return $this;
    }

    public function getContractType(): ?int
    {
        return $this->contractType;
    }

    /**
     * @Groups("export")
     */
    public function getContractTypeToString(): ?string
    {
        return $this->contractType ? EvalProfPerson::CONTRACT_TYPE[$this->contractType] : null;
    }

    public function setContractType(?int $contractType): self
    {
        $this->contractType = $contractType;

        return $this;
    }

    public function getDebt(): ?int
    {
        return $this->debt;
    }

    /**
     * @Groups("export")
     */
    public function getDebtToString(): ?string
    {
        return $this->debt ? EvaluationChoices::YES_NO[$this->debt] : null;
    }

    public function setDebt(?int $debt): self
    {
        $this->debt = $debt;

        return $this;
    }

    /**
     * @Groups("export")
     */
    public function getDebtsAmt(): ?float
    {
        return $this->debtsAmt;
    }

    public function setDebtsAmt(?float $debtsAmt): self
    {
        $this->debtsAmt = $debtsAmt;

        return $this;
    }

    public function getSupportPerson(): ?SupportPerson
    {
        return $this->supportPerson;
    }

    public function setSupportPerson(?SupportPerson $supportPerson): self
    {
        if ($supportPerson->getEvalInitPerson() !== $this) {
            $supportPerson->setEvalInitPerson($this);
        }

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

    /**
     * @return Collection<EvalInitResource>
     */
    public function getEvalBudgetResources(): Collection
    {
        return $this->evalBudgetResources;
    }

    public function addEvalBudgetResource(EvalInitResource $evalBudgetResource): self
    {
        if (!$this->evalBudgetResources->contains($evalBudgetResource)) {
            $this->evalBudgetResources[] = $evalBudgetResource;
            $evalBudgetResource->setEvalInitPerson($this);
        }

        return $this;
    }

    public function removeEvalBudgetResource(EvalInitResource $evalBudgetResource): self
    {
        if ($this->evalBudgetResources->contains($evalBudgetResource)) {
            $this->evalBudgetResources->removeElement($evalBudgetResource);
        }

        return $this;
    }
}
