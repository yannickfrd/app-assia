<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\EvaluationChoices;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalFamilyPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalFamilyPerson
{
    use SoftDeleteableEntity;

    public const MARITAL_STATUS = [
        1 => 'Célibataire',
        2 => 'Concubin·e / Vie maritale',
        3 => 'Divorcé·e',
        4 => 'Marié·e',
        5 => 'Pacsé·e',
        6 => 'Séparé·e',
        7 => 'Veuf/ve',
        97 => 'Autre',
        99 => 'Non évalué',
    ];

    public const PREGNANCY_TYPE = [
        1 => 'Simple',
        2 => 'Jumeaux',
        3 => 'Multiple',
        99 => 'Non évalué',
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
        99 => 'Non évalué',
    ];

    public const CHILDCARE_SCHOOL = [
        3 => 'Famille',
        4 => 'Assistante maternelle',
        1 => 'Crèche',
        2 => 'École',
        5 => 'Collège',
        6 => 'Lycée',
        7 => 'Enseignement supérieur',
        97 => 'Autre',
        99 => 'Non évalué',
    ];

    public const CHILD_TO_HOST = [
        1 => 'En permanence',
        2 => 'En garde alternée',
        3 => 'Journée uniquement',
        4 => 'Uniquemt le WE et congés',
        5 => 'Par un tiers',
        97 => 'Autre',
        99 => 'Non évalué',
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
    private $maritalStatus;

    /** @Groups("export") */
    private $maritalStatusToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $noConciliationOrder;

    /** @Groups("export") */
    private $noConciliationOrderToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $unbornChild;

    /** @Groups("export") */
    private $unbornChildToString;

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
    private $childcareOrSchool;

    /** @Groups("export") */
    private $childcareOrSchoolToString;

    /**
     * @ORM\Column(name="childcare_school", type="smallint", nullable=true)
     */
    private $childcareSchoolType;

    /** @Groups("export") */
    private $childcareSchoolTypeToString;

    /**
     * @ORM\Column(name="childcare_school_location", type="string", length=255, nullable=true)
     */
    private $schoolChildCarePrecision;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $schoolAddress;

    private $schoolFullAddress;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("export")
     */
    private $schoolCity;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $schoolZipcode;

    /** @Groups("export") */
    private $schoolDept;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childToHost;

    /** @Groups("export") */
    private $childToHostToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childDependance;

    /** @Groups("export") */
    private $childDependanceToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $protectiveMeasure;

    /** @Groups("export") */
    private $protectiveMeasureToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $protectiveMeasureType;

    /** @Groups("export") */
    private $protectiveMeasureTypeToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $pmiFollowUp;

    /** @Groups("export") */
    private $pmiFollowUpToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pmiName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalFamilyPerson;

    /**
     * @ORM\OneToOne(targetEntity=EvaluationPerson::class, mappedBy="evalFamilyPerson")
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

    public function getNoConciliationOrderToString(): ?string
    {
        return $this->noConciliationOrder ? EvaluationChoices::YES_NO[$this->noConciliationOrder] : null;
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

    public function getUnbornChildToString(): ?string
    {
        return $this->unbornChild ? EvaluationChoices::YES_NO[$this->unbornChild] : null;
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

    public function getChildcareOrSchool(): ?int
    {
        return $this->childcareOrSchool;
    }

    public function getChildcareOrSchoolToString(): ?string
    {
        return $this->childcareOrSchool ? EvaluationChoices::YES_NO[$this->childcareOrSchool] : null;
    }

    public function setChildcareOrSchool(?int $childcareOrSchool): self
    {
        $this->childcareOrSchool = $childcareOrSchool;

        return $this;
    }

    public function getChildcareSchoolType(): ?int
    {
        return $this->childcareSchoolType;
    }

    public function getChildcareSchoolTypeToString(): ?string
    {
        return $this->childcareSchoolType ? self::CHILDCARE_SCHOOL[$this->childcareSchoolType] : null;
    }

    public function setChildcareSchoolType(?int $childcareSchoolType): self
    {
        $this->childcareSchoolType = $childcareSchoolType;

        return $this;
    }

    public function getSchoolAddress(): ?string
    {
        return $this->schoolAddress;
    }

    public function setSchoolAddress(?string $schoolAddress): self
    {
        $this->schoolAddress = $schoolAddress;

        return $this;
    }

    public function getSchoolFullAddress(): ?string
    {
        if (null === $this->schoolCity) {
            return null;
        }

        return $this->schoolCity.' ('.$this->schoolZipcode.')';
    }

    public function setSchoolFullAddress(?string $schoolFullAddress): self
    {
        $this->schoolFullAddress = $schoolFullAddress;

        return $this;
    }

    public function getSchoolCity(): ?string
    {
        return $this->schoolCity;
    }

    public function setSchoolCity(?string $schoolCity): self
    {
        $this->schoolCity = $schoolCity;

        return $this;
    }

    public function getSchoolZipcode(): ?string
    {
        return $this->schoolZipcode;
    }

    public function setSchoolZipcode(?string $schoolZipcode): self
    {
        $this->schoolZipcode = $schoolZipcode;

        return $this;
    }

    public function getSchoolDept(): ?string
    {
        return $this->schoolZipcode ? substr($this->schoolZipcode, 0, 2) : null;
    }

    public function getSchoolChildCarePrecision(): ?string
    {
        return $this->schoolChildCarePrecision;
    }

    public function setSchoolChildCarePrecision(?string $schoolChildCarePrecision): self
    {
        $this->schoolChildCarePrecision = $schoolChildCarePrecision;

        return $this;
    }

    public function getChildToHost(): ?int
    {
        return $this->childToHost;
    }

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

    public function getProtectiveMeasureToString(): ?string
    {
        return $this->protectiveMeasure ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->protectiveMeasure] : null;
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

    public function getProtectiveMeasureTypeToString(): ?string
    {
        return $this->protectiveMeasureType ? self::PROTECTIVE_MEASURE_TYPE[$this->protectiveMeasureType] : null;
    }

    public function setProtectiveMeasureType(?int $protectiveMeasureType): self
    {
        $this->protectiveMeasureType = $protectiveMeasureType;

        return $this;
    }

    public function getPmiFollowUp(): ?int
    {
        return $this->pmiFollowUp;
    }

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

    public function getCommentEvalFamilyPerson(): ?string
    {
        return $this->commentEvalFamilyPerson;
    }

    public function setCommentEvalFamilyPerson(?string $commentEvalFamilyPerson): self
    {
        $this->commentEvalFamilyPerson = $commentEvalFamilyPerson;

        return $this;
    }

    public function getEvaluationPerson(): EvaluationPerson
    {
        return $this->evaluationPerson;
    }

    public function setEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        if ($evaluationPerson->getEvalFamilyPerson() !== $this) {
            $evaluationPerson->setEvalFamilyPerson($this);
        }

        return $this;
    }
}
