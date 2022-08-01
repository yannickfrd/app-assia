<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\EvaluationChoices;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalProfPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalProfPerson
{
    use SoftDeleteableEntity;

    public const SCHOOL_LEVEL = [
        1 => 'Savoir de base non acquis, illettrisme',
        2 => 'Avant 3ème',
        3 => 'Fin de scolarité obligatoire',
        4 => 'BEP / CAP',
        5 => 'Bac pro',
        6 => 'Bac général',
        7 => 'Bac +2',
        8 => 'Bac +3 (licence)',
        9 => 'Bac +5 (master) et plus',
        97 => 'Autre',
        99 => 'Non évalué',
    ];

    public const PROF_EXPERIENCE = [
        1 => 'Jamais travaillé',
        2 => 'Très peu travaillé',
        3 => 'Alternance emploi et chômage',
        4 => 'A toujours travaillé',
        99 => 'Non évalué',
    ];

    public const PROF_STATUS_EMPLOYEE = 8;
    public const PROF_STATUS_IN_TRAINING = 3;
    public const PROF_STATUS_JOB_SEEKER = 2;
    public const PROF_STATUS_RETIRED = 7;
    public const PROF_STATUS_STUDENT = 5;

    public const PROF_STATUS = [
        1 => 'Auto-entrepreneur/euse',
        2 => "Demandeur/euse d'emploi",
        3 => 'En formation',
        4 => 'En invalidité',
        5 => 'Étudiant·e',
        6 => 'Indépendant·e',
        9 => 'Inactif/ve',
        7 => 'Retraité·e',
        8 => 'Salarié·e',
        97 => 'Autre',
        98 => 'Non concerné',
        99 => 'Non évalué',
    ];

    public const CONTRACT_TYPE = [
        1 => 'CDD',
        2 => 'CDI',
        3 => 'Contrat aidé',
        4 => "Contrat d'apprentissage",
        5 => 'Contrat de professionnalisation',
        6 => 'Statut de la Fonction Publique',
        7 => 'Intérim (CTT)',
        9 => 'Service Civique',
        8 => 'Stage',
        97 => 'Autre',
        98 => 'Non concerné',
        99 => 'Non évalué',
    ];

    public const WORKING_TIME = [
        1 => 'Temps complet ou supérieur à 28h',
        2 => 'Temps partiel – Entre 17,5h et 28h',
        3 => 'Temps partiel – Moins de 17,5h',
        98 => 'Non concerné',
        99 => 'Non évalué',
    ];

    public const TRANSFORT_MEANS = [
        1 => 'Voiture',
        2 => 'Transport en commun',
        3 => 'Moto - Scooter',
        4 => 'Vélo - Trottinette',
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
    private $profStatus;

    /** @Groups("export") */
    private $profStatusToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jobCenterId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $contractType;

    /** @Groups("export") */
    private $contractTypeToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $workingTime;

    /** @Groups("export") */
    private $workingTimeToString;

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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $jobType;

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

    /** @Groups("export") */
    private $transportMeansTypeToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $transportMeans;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $rqth;

    /** @Groups("export") */
    private $rqthToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endRqthDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $schoolLevel;

    /** @Groups("export") */
    private $schoolLevelToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $profExperience;

    /** @Groups("export") */
    private $profExperienceToString;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalProf;

    /**
     * @ORM\OneToOne(targetEntity=EvaluationPerson::class, mappedBy="evalProfPerson")
     */
    private $evaluationPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfStatus(): ?int
    {
        return $this->profStatus;
    }

    public function getProfStatusToString(): ?string
    {
        return $this->profStatus ? self::PROF_STATUS[$this->profStatus] : null;
    }

    public function setProfStatus(?int $profStatus): self
    {
        $this->profStatus = $profStatus;

        return $this;
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

    public function getJobCenterId(): ?string
    {
        return $this->jobCenterId;
    }

    public function setJobCenterId(?string $jobCenterId): self
    {
        $this->jobCenterId = $jobCenterId;

        return $this;
    }

    public function getContractType(): ?int
    {
        return $this->contractType;
    }

    public function getContractTypeToString(): ?string
    {
        return $this->contractType ? self::CONTRACT_TYPE[$this->contractType] : null;
    }

    public function setContractType(?int $contractType): self
    {
        $this->contractType = $contractType;

        return $this;
    }

    public function getWorkingTime(): ?int
    {
        return $this->workingTime;
    }

    public function getWorkingTimeToString(): ?string
    {
        return $this->workingTime ? self::WORKING_TIME[$this->workingTime] : null;
    }

    public function setWorkingTime(?int $workingTime): self
    {
        $this->workingTime = $workingTime;

        return $this;
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

    public function getTransportMeansTypeToString(): ?string
    {
        return $this->transportMeansType ? self::TRANSFORT_MEANS[$this->transportMeansType] : null;
    }

    public function setTransportMeansType(?int $transportMeansType): self
    {
        $this->transportMeansType = $transportMeansType;

        return $this;
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

    public function getRqthToString(): ?string
    {
        return $this->rqth ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->rqth] : null;
    }

    public function setRqth(?int $rqth): self
    {
        $this->rqth = $rqth;

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

    public function getSchoolLevel(): ?int
    {
        return $this->schoolLevel;
    }

    public function getSchoolLevelToString(): ?string
    {
        return $this->schoolLevel ? self::SCHOOL_LEVEL[$this->schoolLevel] : null;
    }

    public function setSchoolLevel(?int $schoolLevel): self
    {
        $this->schoolLevel = $schoolLevel;

        return $this;
    }

    public function getProfExperience(): ?int
    {
        return $this->profExperience;
    }

    public function getProfExperienceToString(): ?string
    {
        return $this->profExperience ? self::PROF_EXPERIENCE[$this->profExperience] : null;
    }

    public function setProfExperience(?int $profExperience): self
    {
        $this->profExperience = $profExperience;

        return $this;
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

    public function getEvaluationPerson(): EvaluationPerson
    {
        return $this->evaluationPerson;
    }

    public function setEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        if ($evaluationPerson->getEvalProfPerson() !== $this) {
            $evaluationPerson->setEvalProfPerson($this);
        }

        return $this;
    }
}
