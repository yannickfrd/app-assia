<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\Choices;
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
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("export")
     */
    private $nbDependentChildren;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cafId;

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

    public function getNbDependentChildren(): ?int
    {
        return $this->nbDependentChildren;
    }

    public function setNbDependentChildren(?int $nbDependentChildren): self
    {
        $this->nbDependentChildren = $nbDependentChildren;

        return $this;
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

    /**
     * @Groups("export")
     */
    public function getChildrenBehindToString(): ?string
    {
        return $this->childrenBehind ? Choices::YES_NO[$this->childrenBehind] : null;
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

    public function getCafId(): ?string
    {
        return $this->cafId;
    }

    public function setCafId(?string $cafId): self
    {
        $this->cafId = $cafId;

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
