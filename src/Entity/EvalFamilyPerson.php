<?php

namespace App\Entity;

use App\Form\Utils\Choices;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalFamilyPersonRepository")
 */
class EvalFamilyPerson
{
    public const MARITAL_STATUS = [
        1 => 'Célibataire',
        2 => 'Concubin·e',
        3 => 'Divorcé·e',
        4 => 'Marié·e',
        5 => 'Pacsé·e',
        6 => 'Séparé·e',
        7 => 'Veuf/ve',
        8 => 'Vie maritale',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const PREGNANCY_TYPE = [
        1 => 'Simple',
        2 => 'Jumeaux',
        3 => 'Multiple',
        99 => 'Non renseigné',
    ];

    public const PROTECTIVE_MEASURE_TYPE = [
        2 => 'Curatelle simple',
        3 => 'Curatelle renforcée',
        6 => 'Habilitation familiale',
        5 => 'Habilitation judiciaire pour représentation du conjoint',
        8 => 'Mandat de protection future',
        7 => "Mesure d'accompagnement (MASP ou MAJ)",
        4 => 'Sauvegarde de justice',
        1 => 'Tutelle',
        97 => 'Autre',
        98 => 'Non concerné',
        99 => 'Non renseigné',
    ];

    public const CHILDCARE_SCHOOL = [
        4 => 'Assistante maternelle',
        1 => 'Crèche',
        2 => 'Ecole',
        3 => 'Famille',
        5 => 'Nourrice',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const CHILD_TO_HOST = [
        1 => 'En permanence',
        2 => 'En garde alternée',
        3 => 'Journée uniquement',
        4 => 'Uniquemt le WE et congés',
        5 => 'Par un tiers',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const CHILD_DEPENDANCE = [
        1 => 'À charge (sans jugement)',
        2 => 'À charge (avec jugement)',
        3 => 'Non à charge',
        4 => 'ASE / placé',
        5 => 'Tiers',
        6 => 'Garde alternée',
        7 => "Droit d'hébergement",
        8 => 'Droit de visite',
        9 => "À l'étranger",
        97 => 'Autre',
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
     */
    private $maritalStatus;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $noConciliationOrder;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $unbornChild;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $expDateChildbirth;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pregnancyType;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childcareSchool;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $childcareSchoolLocation;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childToHost;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childDependance;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $protectiveMeasure;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $protectiveMeasureType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalFamilyPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvaluationPerson", inversedBy="evalFamilyPerson", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $evaluationPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMaritalStatus(): ?int
    {
        return $this->maritalStatus;
    }

    /**
     * @Groups("export")
     */
    public function getMaritalStatusToString(): ?string
    {
        return $this->maritalStatus ? self::MARITAL_STATUS[$this->maritalStatus] : null;
    }

    public function setMaritalStatus(?int $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getNoConciliationOrder(): ?int
    {
        return $this->noConciliationOrder;
    }

    /**
     * @Groups("export")
     */
    public function getNoConciliationOrderToString(): ?string
    {
        return $this->noConciliationOrder ? Choices::YES_NO[$this->noConciliationOrder] : null;
    }

    public function setNoConciliationOrder(?int $noConciliationOrder): self
    {
        $this->noConciliationOrder = $noConciliationOrder;

        return $this;
    }

    public function getUnbornChild(): ?int
    {
        return $this->unbornChild;
    }

    /**
     * @Groups("export")
     */
    public function getUnbornChildToString(): ?string
    {
        return $this->unbornChild ? Choices::YES_NO[$this->unbornChild] : null;
    }

    public function setUnbornChild(?int $unbornChild): self
    {
        $this->unbornChild = $unbornChild;

        return $this;
    }

    public function getExpDateChildbirth(): ?\DateTimeInterface
    {
        return $this->expDateChildbirth;
    }

    public function setExpDateChildbirth(?\DateTimeInterface $expDateChildbirth): self
    {
        $this->expDateChildbirth = $expDateChildbirth;

        return $this;
    }

    public function getPregnancyType(): ?int
    {
        return $this->pregnancyType;
    }

    public function getPregnancyTypeToString(): ?string
    {
        return $this->pregnancyType ? self::PREGNANCY_TYPE[$this->pregnancyType] : null;
    }

    public function setPregnancyType(?int $pregnancyType): self
    {
        $this->pregnancyType = $pregnancyType;

        return $this;
    }

    public function getChildcareSchool(): ?int
    {
        return $this->childcareSchool;
    }

    /**
     * @Groups("export")
     */
    public function getChildcareSchoolToString(): ?string
    {
        return $this->childcareSchool ? self::CHILDCARE_SCHOOL[$this->childcareSchool] : null;
    }

    public function setChildcareSchool(?int $childcareSchool): self
    {
        $this->childcareSchool = $childcareSchool;

        return $this;
    }

    public function getChildcareSchoolLocation(): ?string
    {
        return $this->childcareSchoolLocation;
    }

    public function setChildcareSchoolLocation(?string $childcareSchoolLocation): self
    {
        $this->childcareSchoolLocation = $childcareSchoolLocation;

        return $this;
    }

    public function getChildToHost(): ?int
    {
        return $this->childToHost;
    }

    /**
     * @Groups("export")
     */
    public function getChildToHostToString(): ?string
    {
        return $this->childToHost ? self::CHILD_TO_HOST[$this->childToHost] : null;
    }

    public function setChildToHost(?int $childToHost): self
    {
        $this->childToHost = $childToHost;

        return $this;
    }

    public function getChildDependance(): ?int
    {
        return $this->childDependance;
    }

    /**
     * @Groups("export")
     */
    public function getChildDependanceToString(): ?string
    {
        return $this->childDependance ? self::CHILD_DEPENDANCE[$this->childDependance] : null;
    }

    public function setChildDependance(?int $childDependance): self
    {
        $this->childDependance = $childDependance;

        return $this;
    }

    public function getProtectiveMeasure(): ?int
    {
        return $this->protectiveMeasure;
    }

    /**
     * @Groups("export")
     */
    public function getProtectiveMeasureToString(): ?string
    {
        return $this->protectiveMeasure ? Choices::YES_NO_IN_PROGRESS[$this->protectiveMeasure] : null;
    }

    public function setProtectiveMeasure(?int $protectiveMeasure): self
    {
        $this->protectiveMeasure = $protectiveMeasure;

        return $this;
    }

    public function getProtectiveMeasureType(): ?int
    {
        return $this->protectiveMeasureType;
    }

    /**
     * @Groups("export")
     */
    public function getProtectiveMeasureTypeToString(): ?string
    {
        return $this->protectiveMeasureType ? self::PROTECTIVE_MEASURE_TYPE[$this->protectiveMeasureType] : null;
    }

    public function setProtectiveMeasureType(?int $protectiveMeasureType): self
    {
        $this->protectiveMeasureType = $protectiveMeasureType;

        return $this;
    }

    public function getCommentEvalFamilyPerson(): ?string
    {
        return $this->commentEvalFamilyPerson;
    }

    public function setCommentEvalFamilyPerson(?string $commentEvalFamilyPerson): self
    {
        $this->commentEvalFamilyPerson = $commentEvalFamilyPerson;

        return $this;
    }

    public function getEvaluationPerson(): ?EvaluationPerson
    {
        return $this->evaluationPerson;
    }

    public function setEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        $this->evaluationPerson = $evaluationPerson;

        return $this;
    }
}
