<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\Choices;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalSocialGroupRepository")
 */
class EvalSocialGroup
{
    public const REASON_REQUEST = [
        1 => 'Absence de ressource',
        2 => 'Départ du département initial',
        3 => 'Dort dans la rue',
        4 => 'Exil économique',
        5 => 'Exil familial',
        6 => 'Exil politique',
        7 => 'Exil soins',
        8 => 'Exil autre motif',
        9 => 'Expulsion locative',
        10 => 'Fin de prise en charge ASE',
        11 => "Fin d'hébergement chez des tiers",
        12 => "Fin d'hospitalisation",
        13 => 'Fin prise en charge Conseil Départemental',
        14 => 'Grande exclusion',
        15 => 'Inadaptation du logement',
        16 => 'Logement insalubre',
        17 => 'Logement repris par le propriétaire',
        18 => 'Rapprochement du lieu de travail',
        19 => 'Regroupement familial',
        20 => "Risque d'expulsion locative",
        21 => 'Séparation ou rupture des liens familiaux',
        22 => 'Sortie de détention',
        23 => 'Sortie de logement accompagné',
        24 => "Sortie d'hébergement",
        25 => 'Sortie dispositif asile',
        26 => 'Traite humaine',
        27 => 'Violences familiales ou conjugales',
        97 => 'Autre',
        99 => 'Non évalué',
    ];

    public const WANDERING_TIME = [
        1 => "Moins d'une semaine",
        2 => '1 semaine - 1 mois',
        3 => '1 mois - 6 mois',
        4 => '6 mois - 1 an',
        5 => '1 an - 2 ans',
        6 => '2 ans - 5 ans',
        7 => '5 ans - 10 ans',
        8 => 'Plus de 10 ans',
        97 => 'Autre',
        98 => 'Non concerné',
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
    private $reasonRequest;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $wanderingTime;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $animal;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $animalType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalSocialGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvaluationGroup", inversedBy="evalSocialGroup", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $evaluationGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReasonRequest(): ?int
    {
        return $this->reasonRequest;
    }

    public function setReasonRequest(?int $reasonRequest): self
    {
        $this->reasonRequest = $reasonRequest;

        return $this;
    }

    /**
     * @Groups("export")
     */
    public function getReasonRequestToString(): ?string
    {
        return $this->reasonRequest ? self::REASON_REQUEST[$this->reasonRequest] : null;
    }

    public function getWanderingTime(): ?int
    {
        return $this->wanderingTime;
    }

    public function setWanderingTime(?int $wanderingTime): self
    {
        $this->wanderingTime = $wanderingTime;

        return $this;
    }

    /**
     * @Groups("export")
     */
    public function getWanderingTimeToString(): ?string
    {
        return $this->wanderingTime ? self::WANDERING_TIME[$this->wanderingTime] : null;
    }

    public function getEvaluationGroup(): ?EvaluationGroup
    {
        return $this->evaluationGroup;
    }

    public function getAnimal(): ?int
    {
        return $this->animal;
    }

    /**
     * @Groups("export")
     */
    public function getAnimalToString(): ?string
    {
        return $this->animal ? Choices::YES_NO[$this->animal] : null;
    }

    public function setAnimal(?int $animal): self
    {
        $this->animal = $animal;

        return $this;
    }

    public function getAnimalType(): ?string
    {
        return $this->animalType;
    }

    public function setAnimalType(?string $animalType): self
    {
        $this->animalType = $animalType;

        return $this;
    }

    public function setEvaluationGroup(EvaluationGroup $evaluationGroup): self
    {
        $this->evaluationGroup = $evaluationGroup;

        return $this;
    }

    public function getCommentEvalSocialGroup(): ?string
    {
        return $this->commentEvalSocialGroup;
    }

    public function setCommentEvalSocialGroup(?string $commentEvalSocialGroup): self
    {
        $this->commentEvalSocialGroup = $commentEvalSocialGroup;

        return $this;
    }
}
