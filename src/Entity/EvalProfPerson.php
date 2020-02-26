<?php

namespace App\Entity;

use App\Form\Utils\Choices;
use App\Entity\EvaluationPerson;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalProfPersonRepository")
 */
class EvalProfPerson
{

    public const SCHOOL_LEVEL = [
        1 => "Savoir de base non acquis, illettrisme",
        2 => "Avant 3ème",
        3 => "Fin de scolarité obligatoire",
        4 => "BEP / CAP",
        5 => "Bac pro",
        6 => "Bac général",
        7 => "Bac +2",
        8 => "Bac +3 (licence)",
        9 => "Bac +5 (master) et plus",
        97 => "Autre",
        99 => "Non renseigné"
    ];

    public const PROF_EXPERIENCE = [
        1 => "Jamais travaillé",
        2 => "Très peu travaillé",
        3 => "Alternance emploi et chômage",
        4 => "A toujours travaillé",
        99 => "Non renseigné"
    ];

    public const PROF_STATUS = [
        1 => "Auto-entrepreneur/euse",
        2 => "Demandeur/euse d'emploi",
        3 => "En formation",
        4 => "En invalidité",
        5 => "Étudiant·e",
        6 => "Indépendant·e",
        7 => "Retraité·e",
        8 => "Salarié·e",
        97 => "Autre",
        99 => "Non renseigné"
    ];

    public const CONTRACT_TYPE = [
        1 => "CDD",
        2 => "CDI",
        3 => "Contrat aidé",
        4 => "Contrat d'apprentissage",
        5 => "Contrat de professionnalisation",
        6 => "Fonction publique",
        7 => "Intérim (CTT)",
        8 => "Stage",
        97 => "Autre",
        99 => "Non renseigné"
    ];

    public const TRANSFORT_MEANS = [
        1 => "Voiture",
        2 => "Transport en commun",
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
    private $schoolLevel;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $profExperience;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $profStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jobType;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $contractType;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $contractStartDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $contractEndDate;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $nbWorkingHours;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $workingHours;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $workPlace;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $employerName;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $transportMeansType;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $transportMeans;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $rqth;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalProf;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jobCenterId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endRqthDate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvaluationPerson", inversedBy="evalProfPerson", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $evaluationPerson;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSchoolLevel(): ?int
    {
        return $this->schoolLevel;
    }

    public function setSchoolLevel(?int $schoolLevel): self
    {
        $this->schoolLevel = $schoolLevel;

        return $this;
    }

    public function getSchoolLevelList()
    {
        return self::SCHOOL_LEVEL[$this->schoolLevel];
    }

    public function getProfExperience(): ?int
    {
        return $this->profExperience;
    }

    public function setProfExperience(?int $profExperience): self
    {
        $this->profExperience = $profExperience;

        return $this;
    }

    public function getProfExperienceList()
    {
        return self::PROF_EXPERIENCE[$this->profExperience];
    }

    public function getProfStatus(): ?int
    {
        return $this->profStatus;
    }

    public function setProfStatus(?int $profStatus): self
    {
        $this->profStatus = $profStatus;

        return $this;
    }

    public function getProfStatusList()
    {
        return self::PROF_STATUS[$this->profStatus];
    }

    public function getJobType(): ?string
    {
        return $this->jobType;
    }

    public function setJobType(?string $jobType): self
    {
        $this->jobType = $jobType;

        return $this;
    }

    public function getContractType(): ?int
    {
        return $this->contractType;
    }

    public function setContractType(?int $contractType): self
    {
        $this->contractType = $contractType;

        return $this;
    }

    public function getContractTypeList()
    {
        return self::CONTRACT_TYPE[$this->contractType];
    }

    public function getContractStartDate(): ?\DateTimeInterface
    {
        return $this->contractStartDate;
    }

    public function setContractStartDate(?\DateTimeInterface $contractStartDate): self
    {
        $this->contractStartDate = $contractStartDate;

        return $this;
    }

    public function getContractEndDate(): ?\DateTimeInterface
    {
        return $this->contractEndDate;
    }

    public function setContractEndDate(?\DateTimeInterface $contractEndDate): self
    {
        $this->contractEndDate = $contractEndDate;

        return $this;
    }

    public function getNbWorkingHours(): ?string
    {
        return $this->nbWorkingHours;
    }

    public function setNbWorkingHours(?string $nbWorkingHours): self
    {
        $this->nbWorkingHours = $nbWorkingHours;

        return $this;
    }

    public function getWorkingHours(): ?string
    {
        return $this->workingHours;
    }

    public function setWorkingHours(?string $workingHours): self
    {
        $this->workingHours = $workingHours;

        return $this;
    }

    public function getWorkPlace(): ?string
    {
        return $this->workPlace;
    }

    public function setWorkPlace(?string $workPlace): self
    {
        $this->workPlace = $workPlace;

        return $this;
    }

    public function getEmployerName(): ?string
    {
        return $this->employerName;
    }

    public function setEmployerName(?string $employerName): self
    {
        $this->employerName = $employerName;

        return $this;
    }

    public function getTransportMeansType(): ?int
    {
        return $this->transportMeansType;
    }

    public function setTransportMeansType(?int $transportMeansType): self
    {
        $this->transportMeansType = $transportMeansType;

        return $this;
    }

    public function getTransportMeansTypeList()
    {
        return self::TRANSFORT_MEANS[$this->transportMeansType];
    }


    public function getTransportMeans(): ?string
    {
        return $this->transportMeans;
    }

    public function setTransportMeans(?string $transportMeans): self
    {
        $this->transportMeans = $transportMeans;

        return $this;
    }

    public function getRqth(): ?int
    {
        return $this->rqth;
    }

    public function setRqth(?int $rqth): self
    {
        $this->rqth = $rqth;

        return $this;
    }

    public function getRqthList()
    {
        return Choices::YES_NO_IN_PROGRESS[$this->rqth];
    }


    public function getCommentEvalProf(): ?string
    {
        return $this->commentEvalProf;
    }

    public function setCommentEvalProf(?string $commentEvalProf): self
    {
        $this->commentEvalProf = $commentEvalProf;

        return $this;
    }

    public function getJobCenterId(): ?string
    {
        return $this->jobCenterId;
    }

    public function setJobCenterId(?string $jobCenterId): self
    {
        $this->jobCenterId = $jobCenterId;

        return $this;
    }

    public function getEndRqthDate(): ?\DateTimeInterface
    {
        return $this->endRqthDate;
    }

    public function setEndRqthDate(?\DateTimeInterface $endRqthDate): self
    {
        $this->endRqthDate = $endRqthDate;

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
