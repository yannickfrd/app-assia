<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalJusticePersonRepository")
 */
class EvalJusticePerson
{
    public const JUSTICE_STATUS = [
        1 => "Contrainte pénale",
        2 => "Contrôle judiciaire (CJ)", // Sursis probatoire
        3 => "Convocation sur procès-verbal en matière pénale (CPPV)",
        4 => "Détention",
        5 => "Libération conditionnelle (LC)",
        6 => "Placement extérieur (PE)",
        7 => "Placement sous surveillannce électronique (PSE)", // détention à domicile sous surveillance électronique (DDSE) 
        8 => "Semi-liberté",
        9 => "Sortie de détention",
        10 => "Suivi socio-judiciaire (SSJ)",
        11 => "Sursis de mise à l'épreuve (SME)",
        12 => "Suspension de peine pour raison médicale",
        13 => "Travail d'intérêt général (TIG)",
        97 => "Autre",
        98 => "Non concerné",
        99 => "Non renseigné"
    ];

    public const JUSTICE_ACT = [
        1 => "Composition pénale (CP)",
        2 => "Contrôle judiciaire socio-éductatif (CJS)", // Sursis probatoire
        3 => "Enquête de personnalité auteur (EP)",
        4 => "Enquête de personnalité victime",
        5 => "Enquête sociale rapide",
        6 => "Placement extérieur (PE)",
        7 => "Placement sous surveillannce électronique (PSE)", // détention à domicile sous surveillance électronique (DDSE) 
        8 => "Placement sous surveillannce électronique mobile (PSEM)", // détention à domicile sous surveillance électronique (DDSE) 
        9 => "Libération conditionnelle (LC)",
        10 => "Réduction conditionnelle de peine",
        11 => "Stage de citoyenneté",
        12 => "Stage de sensibilisation",
        13 => "Suivi socio-judiciaire (SSJ)",
        14 => "Sursis de mise à l'épreuve (SME)",
        14 => "Travail d'intérêt général (TIG)",
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
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $justiceAct;

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

    public function getJusticeAct(): ?int
    {
        return $this->justiceAct;
    }

    public function setJusticeAct(?int $justiceAct): self
    {
        $this->justiceAct = $justiceAct;

        return $this;
    }

    public function getJusticeActList()
    {
        return self::JUSTICE_ACT[$this->justiceAct];
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
