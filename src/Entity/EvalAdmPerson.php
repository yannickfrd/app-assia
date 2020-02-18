<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalAdmPersonRepository")
 */
class EvalAdmPerson
{
    public const NATIONALITY = [
        1 => "France",
        2 => "Union-Européenne",
        3 => "Hors-UE",
        4 => "Apatride",
        99 => "Non renseignée"
    ];

    public const PAPER_TYPE = [
        4 => "Carte de résident",
        5 => "Carte de séjour temporaire",
        1 => "CNI",
        8 => "Déclaration de perte",
        3 => "Papiers étrangers",
        2 => "Passeport",
        6 => "Récépissé asile",
        7 => "Récépissé renouvellemt de titre",
        97 => "Autre",
        99 => "Non renseigné"
    ];

    public const RIGHT_TO_RESIDE = [
        1 => "Débouté du droit d'asile",
        2 => "Demandeur d'asile",
        3 => "Réfugié",
        97 => "Autre",
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
    private $nationality;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $country;

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
    private $asylumBackground;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $rightReside;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $residPermitRequest;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endValidPermitDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $renewalPermitDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $nbRenewals;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $noRightsOpen;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $rightWork;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $rightSocialBenf;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $housingAlw;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalAdmPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvaluationPerson", inversedBy="evalAdmPerson", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $evaluationPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNationality(): ?int
    {
        return $this->nationality;
    }

    public function setNationality(?int $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getNationalityList()
    {
        return self::NATIONALITY[$this->nationality];
    }


    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPaper(): ?int
    {
        return $this->paper;
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

    public function setPaperType(?int $paperType): self
    {
        $this->paperType = $paperType;

        return $this;
    }

    public function getPaperTypeList()
    {
        return self::PAPER_TYPE[$this->paperType];
    }


    public function getAsylumBackground(): ?int
    {
        return $this->asylumBackground;
    }

    public function setAsylumBackground(?int $asylumBackground): self
    {
        $this->asylumBackground = $asylumBackground;

        return $this;
    }

    public function getRightReside(): ?int
    {
        return $this->rightReside;
    }

    public function setRightReside(?int $rightReside): self
    {
        $this->rightReside = $rightReside;

        return $this;
    }

    public function getRightResideList()
    {
        return self::RIGHT_TO_RESIDE[$this->rightReside];
    }


    public function getResidPermitRequest(): ?int
    {
        return $this->residPermitRequest;
    }

    public function setResidPermitRequest(?int $residPermitRequest): self
    {
        $this->residPermitRequest = $residPermitRequest;

        return $this;
    }

    public function getEndValidPermitDate(): ?\DateTimeInterface
    {
        return $this->endValidPermitDate;
    }

    public function setEndValidPermitDate(?\DateTimeInterface $endValidPermitDate): self
    {
        $this->endValidPermitDate = $endValidPermitDate;

        return $this;
    }

    public function getRenewalPermitDate(): ?\DateTimeInterface
    {
        return $this->renewalPermitDate;
    }

    public function setRenewalPermitDate(?\DateTimeInterface $renewalPermitDate): self
    {
        $this->renewalPermitDate = $renewalPermitDate;

        return $this;
    }

    public function getNbRenewals(): ?int
    {
        return $this->nbRenewals;
    }

    public function setNbRenewals(?int $nbRenewals): self
    {
        $this->nbRenewals = $nbRenewals;

        return $this;
    }

    public function getNoRightsOpen(): ?bool
    {
        return $this->noRightsOpen;
    }

    public function setNoRightsOpen(?bool $noRightsOpen): self
    {
        $this->noRightsOpen = $noRightsOpen;

        return $this;
    }

    public function getRightWork(): ?bool
    {
        return $this->rightWork;
    }

    public function setRightWork(?bool $rightWork): self
    {
        $this->rightWork = $rightWork;

        return $this;
    }

    public function getRightSocialBenf(): ?bool
    {
        return $this->rightSocialBenf;
    }

    public function setRightSocialBenf(?bool $rightSocialBenf): self
    {
        $this->rightSocialBenf = $rightSocialBenf;

        return $this;
    }

    public function getHousingAlw(): ?bool
    {
        return $this->housingAlw;
    }

    public function setHousingAlw(?bool $housingAlw): self
    {
        $this->housingAlw = $housingAlw;

        return $this;
    }

    public function getCommentEvalAdmPerson(): ?string
    {
        return $this->commentEvalAdmPerson;
    }

    public function setCommentEvalAdmPerson(?string $commentEvalAdmPerson): self
    {
        $this->commentEvalAdmPerson = $commentEvalAdmPerson;

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
