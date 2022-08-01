<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\EvaluationChoices;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalAdmPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalAdmPerson
{
    use SoftDeleteableEntity;

    public const NATIONALITY_FRANCE = 1;
    public const NATIONALITY_EU = 2;
    public const NATIONALITY_OUTSIDE_EU = 3;

    public const NATIONALITY = [
        1 => 'France',
        2 => 'Union-Européenne',
        3 => 'Hors-UE',
        4 => 'Apatride',
        99 => 'Non évaluée',
    ];

    public const PAPER_TYPE = [
        04 => 'Acte de naissance',
        22 => 'Autorisation provisoire de séjour',
        20 => 'Carte de résident',
        21 => 'Carte de séjour temporaire',
        01 => 'CNI française',
        40 => 'DCEM (Doc. circulation pour étranger mineur)',
        10 => "Demande d'asile",
        03 => 'Papiers étrangers',
        02 => 'Passeport',
        30 => 'Récépissé première demande',
        31 => 'Récépissé renouvellement de titre',
        97 => 'Autre',
        99 => 'Non évalué',
    ];

    public const ASYLUM_STATUS = [
        1 => "Débouté du droit d'asile",
        6 => "Demande d'asile - Procédure accélérée",
        2 => "Demande d'asile - Procédure normale",
        7 => 'Procédure Dublin',
        8 => 'Procédure Schengen',
        3 => 'Protection subsidiaire',
        9 => 'Recours CNDA',
        5 => 'ATDA (attestation temporaire de demande d\'asile)',
        4 => 'Réfugié statutaire',
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
    private $nationality;

    /**
     * @Groups("export")
     */
    private $nationalityToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $arrivalDate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups("export")
     */
    private $country;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paper;

    /**
     * @Groups("export")
     */
    private $paperToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $paperType;

    /**
     * @Groups("export")
     */
    private $paperTypeToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $asylumBackground;

    /**
     * @Groups("export")
     */
    private $asylumBackgroundToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $asylumStatus;

    /**
     * @Groups("export")
     */
    private $asylumStatusToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("export")
     */
    private $agdrefId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("export")
     */
    private $ofpraRegistrationId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cndaId;

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
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $workRight;

    /**
     * @Groups("export")
     */
    private $workRightToString;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalAdmPerson;

    /**
     * @ORM\OneToOne(targetEntity=EvaluationPerson::class, mappedBy="evalAdmPerson")
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

    public function getNationalityToString(): ?string
    {
        return $this->nationality ? self::NATIONALITY[$this->nationality] : null;
    }

    public function setNationality(?int $nationality): self
    {
        $this->nationality = $nationality;

        return $this;
    }

    public function getArrivalDate(): ?\DateTimeInterface
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(?\DateTimeInterface $arrivalDate): self
    {
        $this->arrivalDate = $arrivalDate;

        return $this;
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

    public function getPaperToString(): ?string
    {
        return $this->paper ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->paper] : null;
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

    public function getPaperTypeToString(): ?string
    {
        return $this->paperType ? self::PAPER_TYPE[$this->paperType] : null;
    }

    public function setPaperType(?int $paperType): self
    {
        $this->paperType = $paperType;

        return $this;
    }

    public function getAsylumBackground(): ?int
    {
        return $this->asylumBackground;
    }

    public function getAsylumBackgroundToString(): ?string
    {
        return $this->asylumBackground ? EvaluationChoices::YES_NO[$this->asylumBackground] : null;
    }

    public function setAsylumBackground(?int $asylumBackground): self
    {
        $this->asylumBackground = $asylumBackground;

        return $this;
    }

    public function getAsylumStatus(): ?int
    {
        return $this->asylumStatus;
    }

    public function getAsylumStatusToString(): ?string
    {
        return $this->asylumStatus ? self::ASYLUM_STATUS[$this->asylumStatus] : null;
    }

    public function setAsylumStatus(?int $asylumStatus): self
    {
        $this->asylumStatus = $asylumStatus;

        return $this;
    }

    public function getAgdrefId(): ?string
    {
        return $this->agdrefId;
    }

    public function setAgdrefId(?string $agdrefId): self
    {
        $this->agdrefId = $agdrefId;

        return $this;
    }

    public function getOfpraRegistrationId(): ?string
    {
        return $this->ofpraRegistrationId;
    }

    public function setOfpraRegistrationId(?string $ofpraRegistrationId): self
    {
        $this->ofpraRegistrationId = $ofpraRegistrationId;

        return $this;
    }

    public function getCndaId(): ?string
    {
        return $this->cndaId;
    }

    public function setCndaId(?string $cndaId): self
    {
        $this->cndaId = $cndaId;

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

    public function getWorkRight(): ?int
    {
        return $this->workRight;
    }

    public function getWorkRightToString(): ?string
    {
        return $this->workRight ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->workRight] : null;
    }

    public function setWorkRight(?int $workRight): self
    {
        $this->workRight = $workRight;

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

    public function getEvaluationPerson(): EvaluationPerson
    {
        return $this->evaluationPerson;
    }

    public function setEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        if ($evaluationPerson->getEvalAdmPerson() !== $this) {
            $evaluationPerson->setEvalAdmPerson($this);
        }

        return $this;
    }
}
