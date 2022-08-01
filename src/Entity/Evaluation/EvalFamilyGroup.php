<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\EvaluationChoices;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalFamilyGroupRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalFamilyGroup
{
    use SoftDeleteableEntity;

    public const FAML_REUNIFICATION = [
        1 => 'Oui',
        2 => 'Non',
        3 => 'Envisagé',
        4 => 'En cours',
        5 => 'Accepté',
        99 => 'Non évalué',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childrenBehind;

    /** @Groups("export") */
    private $childrenBehindToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $famlReunification;

    /** @Groups("export") */
    private $famlReunificationToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("export")
     */
    private $nbPeopleReunification;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalFamilyGroup;

    /**
     * @ORM\OneToOne(targetEntity=EvaluationGroup::class, mappedBy="evalFamilyGroup")
     */
    private $evaluationGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChildrenBehind(): ?int
    {
        return $this->childrenBehind;
    }

    public function setChildrenBehind(?int $childrenBehind): self
    {
        $this->childrenBehind = $childrenBehind;

        return $this;
    }

    public function getChildrenBehindToString(): ?string
    {
        return $this->childrenBehind ? EvaluationChoices::YES_NO[$this->childrenBehind] : null;
    }

    public function getFamlReunification(): ?int
    {
        return $this->famlReunification;
    }

    public function setFamlReunification(?int $famlReunification): self
    {
        $this->famlReunification = $famlReunification;

        return $this;
    }

    public function getFamlReunificationToString(): ?string
    {
        return $this->famlReunification ? self::FAML_REUNIFICATION[$this->famlReunification] : null;
    }

    public function getNbPeopleReunification(): ?int
    {
        return $this->nbPeopleReunification;
    }

    public function setNbPeopleReunification(?int $nbPeopleReunification): self
    {
        $this->nbPeopleReunification = $nbPeopleReunification;

        return $this;
    }

    public function getCommentEvalFamilyGroup(): ?string
    {
        return $this->commentEvalFamilyGroup;
    }

    public function setCommentEvalFamilyGroup(?string $commentEvalFamilyGroup): self
    {
        $this->commentEvalFamilyGroup = $commentEvalFamilyGroup;

        return $this;
    }

    public function getEvaluationGroup(): EvaluationGroup
    {
        return $this->evaluationGroup;
    }

    public function setEvaluationGroup(EvaluationGroup $evaluationGroup): self
    {
        if ($evaluationGroup->getEvalFamilyGroup() !== $this) {
            $evaluationGroup->setEvalFamilyGroup($this);
        }

        return $this;
    }
}
