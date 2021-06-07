<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\EvaluationChoices;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalFamilyGroupRepository")
 */
class EvalFamilyGroup
{
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

    // /**
    //  * @ORM\Column(type="smallint", nullable=true)
    //  */
    // private $nbDependentChildren; // TO DELETE

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childrenBehind;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $famlReunification;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("export")
     */
    private $nbPeopleReunification;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pmiFollowUp;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pmiName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalFamilyGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvaluationGroup", inversedBy="evalFamilyGroup")
     * @ORM\JoinColumn(nullable=true)
     */
    private $evaluationGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    // public function getNbDependentChildren(): ?int
    // {
    //     return $this->nbDependentChildren;
    // }

    // public function setNbDependentChildren(?int $nbDependentChildren): self
    // {
    //     $this->nbDependentChildren = $nbDependentChildren;

    //     return $this;
    // }

    public function getChildrenBehind(): ?int
    {
        return $this->childrenBehind;
    }

    public function setChildrenBehind(?int $childrenBehind): self
    {
        $this->childrenBehind = $childrenBehind;

        return $this;
    }

    /**
     * @Groups("export")
     */
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

    /**
     * @Groups("export")
     */
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

    public function getPmiFollowUp(): ?int
    {
        return $this->pmiFollowUp;
    }

    /**
     * @Groups("export")
     */
    public function getPmiFollowUpToString(): ?string
    {
        return $this->pmiFollowUp ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->pmiFollowUp] : null;
    }

    public function setPmiFollowUp(?int $pmiFollowUp): self
    {
        $this->pmiFollowUp = $pmiFollowUp;

        return $this;
    }

    public function getPmiName(): ?string
    {
        return $this->pmiName;
    }

    public function setPmiName(?string $pmiName): self
    {
        $this->pmiName = $pmiName;

        return $this;
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
}
