<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalFamilyPersonRepository")
 */
class EvalFamilyPerson
{
    public const MARITAL_STATUS = [
        1 => "Célibataire",
        2 => "Concubinage",
        3 => "Divorcé·e",
        4 => "Marié·e",
        5 => "Pacsé·e",
        6 => "Séparé·e",
        7 => "Veuf/ve",
        8 => "Vie maritale",
        98 => "Autre",
        99 => "Non renseigné"
    ];

    public const CHILDCARE_SCHOOL = [
        1 => "Crèche",
        2 => "Scolarité",
        98 => "Autre",
        99 => "Non renseigné"
    ];

    public const CHILD_TO_HOST = [
        1 => "En permanence",
        2 => "En garde alternée",
        3 => "Uniquemt le WE et congés",
        4 => "Journée uniquement",
        5 => "Par un tiers",
        98 => "Autre",
        99 => "Non renseigné"
    ];

    public const CHILD_DEPENDANCE = [
        1 => "À charge (sans jugement)",
        2 => "À charge (avec jugement)",
        3 => "Non à charge",
        4 => "ASE / placé",
        5 => "Tiers",
        6 => "Garde alternée",
        7 => "Droit d'hébergement",
        8 => "Droit de visite",
        9 => "À l'étranger",
        98 => "Autre",
        99 => "Non renseigné"
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

    public function setMaritalStatus(?int $maritalStatus): self
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getMaritalStatusList()
    {
        return self::MARITAL_STATUS[$this->maritalStatus];
    }


    public function getChildcareSchool(): ?int
    {
        return $this->childcareSchool;
    }

    public function setChildcareSchool(?int $childcareSchool): self
    {
        $this->childcareSchool = $childcareSchool;

        return $this;
    }

    public function getChildcareSchoolList()
    {
        return self::CHILDCARE_SCHOOL[$this->childcareSchool];
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

    public function setChildToHost(?int $childToHost): self
    {
        $this->childToHost = $childToHost;

        return $this;
    }

    public function getChildToHostList()
    {
        return self::CHILD_TO_HOST[$this->childcareSchool];
    }

    public function getChildDependance(): ?int
    {
        return $this->childDependance;
    }

    public function setChildDependance(?int $childDependance): self
    {
        $this->childDependance = $childDependance;

        return $this;
    }

    public function getChildDependancelist()
    {
        return self::CHILD_DEPENDANCE[$this->childDependance];
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
