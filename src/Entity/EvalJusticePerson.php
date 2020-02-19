<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalJusticePersonRepository")
 */
class EvalJusticePerson
{
    public const JUSTICE_STATUS = [
        2 => "Contrainte pénale",
        1 => "Contrôle Judiciaire",
        3 => "Convocation sur procès-verbal en matière pénale",
        4 => "Détention",
        5 => "Placement extérieur",
        6 => "Placement sous surveillance électronique",
        8 => "Suivi socio-judiciaire",
        7 => "Sursis de mise à l'épreuce",
        9 => "Travail d'intérêt général",
        97 => "Autre",
        98 => "Non concerné",
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
    private $justiceStatus;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalJustice;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvaluationPerson", inversedBy="evalJusticePerson", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $evaluationPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJusticeStatus(): ?int
    {
        return $this->justiceStatus;
    }

    public function setJusticeStatus(?int $justiceStatus): self
    {
        $this->justiceStatus = $justiceStatus;

        return $this;
    }

    public function getJusticeStatusList()
    {
        return self::JUSTICE_STATUS[$this->justiceStatus];
    }

    public function getCommentEvalJustice(): ?string
    {
        return $this->commentEvalJustice;
    }

    public function setCommentEvalJustice(?string $commentEvalJustice): self
    {
        $this->commentEvalJustice = $commentEvalJustice;

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
