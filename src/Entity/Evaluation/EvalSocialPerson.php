<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\EvaluationChoices;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalSocialPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalSocialPerson
{
    use SoftDeleteableEntity;

    public const SOCIAL_SECURITY = [
        6 => 'ACS',
        5 => 'AME',
        4 => 'CSS (ex-CMU-C)',
        3 => 'PUMA (ex-CMU)',
        1 => 'Régime général',
        2 => 'Régime général et mutuelle',
        97 => 'Autre régime',
        99 => 'Non évaluée',
    ];

    public const CARE_SUPPORT = [
        1 => 'Infirmière/er à domicile',
        2 => 'PCH',
        3 => 'SAMSAH',
        4 => 'SAVS',
        97 => 'Autre',
        99 => 'Non évaluée',
    ];

    public const HEALTH_PROBLEMS_TYPE = [
        'physicalHealthProblem' => 'Santé physique',
        'mentalHealthProblem' => 'Santé mentale',
        'addictionProblem' => 'Addiction(s)',
        'reducedMobility' => 'Personne à mobilité réduite',
        'wheelchair' => 'Personne en fauteuil roulant',
    ];

    public const ASE_MEASURE_TYPE = [
        1 => 'AED', //Action éducative à domicile
        2 => 'AGBF', // Aide à la gestion du budget familial
        3 => 'AEMO', // Action éducative en milieu ouvert
        4 => 'AESF', // Accommpagnbement en économie sociale et familiale
        5 => 'MJIE', // Mesure judiciaire d'investigation éducative
        6 => 'Placement', // Placement administratif
        7 => 'TISF', // Technicien d'intervention sociale et familiale
        8 => 'UEMO', // Unité éducative en milieu ouvert
        97 => 'Autre',
        99 => 'Non évaluée',
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
    private $rightSocialSecurity;

    /** @Groups("export") */
    private $rightSocialSecurityToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialSecurity;

    /** @Groups("export") */
    private $socialSecurityToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $socialSecurityOffice;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endRightsSocialSecurityDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $infoCrip;

    /** @Groups("export") */
    private $infoCripToString;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("export")
     */
    private $infoCripDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $infoCripByService;

    /** @Groups("export") */
    private $infoCripByServiceToString;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $infoCripComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $childWelfareBackground; // TO DELETE ?

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $aseFollowUp;

    /** @Groups("export") */
    private $aseFollowUpToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $aseMeasureType;

    /** @Groups("export") */
    private $aseMeasureTypeToString;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $aseComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $healthProblem;

    /** @Groups("export") */
    private $healthProblemToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $physicalHealthProblem;

    /** @Groups("export") */
    private $physicalHealthProblemToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $mentalHealthProblem;

    /** @Groups("export") */
    private $mentalHealthProblemToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $addictionProblem;

    /** @Groups("export") */
    private $addictionProblemToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $reducedMobility;

    /** @Groups("export") */
    private $reducedMobilityToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $wheelchair;

    /** @Groups("export") */
    private $wheelchairToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $medicalFollowUp;

    /** @Groups("export") */
    private $medicalFollowUpToString;

    /**
     * @ORM\Column(name="care_support", type="smallint", nullable=true)
     */
    private $homeCareSupport;

    /** @Groups("export") */
    private $homeCareSupportToString;

    /**
     * @ORM\Column(name="care_support_type", type="smallint", nullable=true)
     */
    private $homeCareSupportType;

    /** @Groups("export") */
    private $homeCareSupportTypeToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $familyBreakdown;

    /** @Groups("export") */
    private $familyBreakdownToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $friendshipBreakdown;

    /** @Groups("export") */
    private $friendshipBreakdownToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $violenceVictim;

    /** @Groups("export") */
    private $violenceVictimToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $domViolenceVictim;

    /** @Groups("export") */
    private $domViolenceVictimToString;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalSocialPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvaluationPerson", inversedBy="evalSocialPerson", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $evaluationPerson;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRightSocialSecurity(): ?int
    {
        return $this->rightSocialSecurity;
    }

    public function getRightSocialSecurityToString(): ?string
    {
        return $this->rightSocialSecurity ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->rightSocialSecurity] : null;
    }

    public function setRightSocialSecurity(?int $rightSocialSecurity): self
    {
        $this->rightSocialSecurity = $rightSocialSecurity;

        return $this;
    }

    public function getSocialSecurity(): ?int
    {
        return $this->socialSecurity;
    }

    public function getSocialSecurityToString(): ?string
    {
        return $this->socialSecurity ? self::SOCIAL_SECURITY[$this->socialSecurity] : null;
    }

    public function setSocialSecurity(?int $socialSecurity): self
    {
        $this->socialSecurity = $socialSecurity;

        return $this;
    }

    public function getSocialSecurityOffice(): ?string
    {
        return $this->socialSecurityOffice;
    }

    public function setSocialSecurityOffice(?string $socialSecurityOffice): self
    {
        $this->socialSecurityOffice = $socialSecurityOffice;

        return $this;
    }

    public function getEndRightsSocialSecurityDate(): ?\DateTimeInterface
    {
        return $this->endRightsSocialSecurityDate;
    }

    public function setEndRightsSocialSecurityDate(?\DateTimeInterface $endRightsSocialSecurityDate): self
    {
        $this->endRightsSocialSecurityDate = $endRightsSocialSecurityDate;

        return $this;
    }

    public function getChildWelfareBackground(): ?int
    {
        return $this->childWelfareBackground;
    }

    public function getInfoCrip(): ?int
    {
        return $this->infoCrip;
    }

    public function getInfoCripToString(): ?string
    {
        return $this->infoCrip ? EvaluationChoices::YES_NO[$this->infoCrip] : null;
    }

    public function setInfoCrip(?int $infoCrip): self
    {
        $this->infoCrip = $infoCrip;

        return $this;
    }

    public function getInfoCripDate(): ?\DateTimeInterface
    {
        return $this->infoCripDate;
    }

    public function setInfoCripDate(?\DateTimeInterface $infoCripDate): self
    {
        $this->infoCripDate = $infoCripDate;

        return $this;
    }

    public function getInfoCripByService(): ?int
    {
        return $this->infoCripByService;
    }

    public function getInfoCripByServiceToString(): ?string
    {
        return $this->infoCripByService ? EvaluationChoices::YES_NO[$this->infoCripByService] : null;
    }

    public function setInfoCripByService(?int $infoCripByService): self
    {
        $this->infoCripByService = $infoCripByService;

        return $this;
    }

    public function getInfoCripComment(): ?string
    {
        return $this->infoCripComment;
    }

    public function setInfoCripComment(?string $infoCripComment): self
    {
        $this->infoCripComment = $infoCripComment;

        return $this;
    }

    public function getChildWelfareBackgroundToString(): ?string
    {
        return $this->childWelfareBackground ? EvaluationChoices::YES_NO[$this->childWelfareBackground] : null;
    }

    public function setChildWelfareBackground(?int $childWelfareBackground): self
    {
        $this->childWelfareBackground = $childWelfareBackground;

        return $this;
    }

    public function getAseFollowUp(): ?int
    {
        return $this->aseFollowUp;
    }

    public function getAseFollowUpToString(): ?string
    {
        return $this->aseFollowUp ? EvaluationChoices::YES_NO[$this->aseFollowUp] : null;
    }

    public function setAseFollowUp(?int $aseFollowUp): self
    {
        $this->aseFollowUp = $aseFollowUp;

        return $this;
    }

    public function getAseMeasureType(): ?int
    {
        return $this->aseMeasureType;
    }

    public function getAseMeasureTypeToString(): ?string
    {
        return $this->aseMeasureType ? self::ASE_MEASURE_TYPE[$this->aseMeasureType] : null;
    }

    public function setAseMeasureType(?int $aseMeasureType): self
    {
        $this->aseMeasureType = $aseMeasureType;

        return $this;
    }

    public function getAseComment(): ?string
    {
        return $this->aseComment;
    }

    public function setAseComment(?string $aseComment): self
    {
        $this->aseComment = $aseComment;

        return $this;
    }

    public function getHealthProblem(): ?int
    {
        return $this->healthProblem;
    }

    public function getHealthProblemToString(): ?string
    {
        return $this->healthProblem ? EvaluationChoices::YES_NO[$this->healthProblem] : null;
    }

    public function setHealthProblem(?int $healthProblem): self
    {
        $this->healthProblem = $healthProblem;

        return $this;
    }

    public function getHealthProblemTypes(): array
    {
        $array = [];

        foreach (self::HEALTH_PROBLEMS_TYPE as $key => $value) {
            $method = 'get'.ucfirst($key);
            if (EvaluationChoices::YES === $this->$method()) {
                $array[] = $value;
            }
        }

        return $array;
    }

    public function getPhysicalHealthProblem(): ?int
    {
        return $this->physicalHealthProblem;
    }

    public function getPhysicalHealthProblemToString(): ?string
    {
        return $this->physicalHealthProblem ? EvaluationChoices::YES_NO_BOOLEAN[$this->physicalHealthProblem] : null;
    }

    public function setPhysicalHealthProblem(?int $physicalHealthProblem): self
    {
        $this->physicalHealthProblem = $physicalHealthProblem;

        return $this;
    }

    public function getMentalHealthProblem(): ?int
    {
        return $this->mentalHealthProblem;
    }

    public function getMentalHealthProblemToString(): ?string
    {
        return $this->mentalHealthProblem ? EvaluationChoices::YES_NO_BOOLEAN[$this->mentalHealthProblem] : null;
    }

    public function setMentalHealthProblem(?int $mentalHealthProblem): self
    {
        $this->mentalHealthProblem = $mentalHealthProblem;

        return $this;
    }

    public function getAddictionProblem(): ?int
    {
        return $this->addictionProblem;
    }

    public function getAddictionProblemToString(): ?string
    {
        return $this->addictionProblem ? EvaluationChoices::YES_NO_BOOLEAN[$this->addictionProblem] : null;
    }

    public function setAddictionProblem(?int $addictionProblem): self
    {
        $this->addictionProblem = $addictionProblem;

        return $this;
    }

    public function getWheelchair(): ?int
    {
        return $this->wheelchair;
    }

    public function getWheelchairToString(): ?string
    {
        return $this->wheelchair ? EvaluationChoices::YES_NO_BOOLEAN[$this->wheelchair] : null;
    }

    public function setWheelchair(?int $wheelchair): self
    {
        $this->wheelchair = $wheelchair;

        return $this;
    }

    public function getReducedMobility(): ?int
    {
        return $this->reducedMobility;
    }

    public function getReducedMobilityToString(): ?string
    {
        return $this->reducedMobility ? EvaluationChoices::YES_NO_BOOLEAN[$this->reducedMobility] : null;
    }

    public function setReducedMobility(?int $reducedMobility): self
    {
        $this->reducedMobility = $reducedMobility;

        return $this;
    }

    public function getMedicalFollowUp(): ?int
    {
        return $this->medicalFollowUp;
    }

    public function getMedicalFollowUpToString(): ?string
    {
        return $this->medicalFollowUp ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->medicalFollowUp] : null;
    }

    public function setMedicalFollowUp(?int $medicalFollowUp): self
    {
        $this->medicalFollowUp = $medicalFollowUp;

        return $this;
    }

    public function getHomeCareSupport(): ?int
    {
        return $this->homeCareSupport;
    }

    public function getHomeCareSupportToString(): ?string
    {
        return $this->homeCareSupport ? EvaluationChoices::YES_NO_IN_PROGRESS[$this->homeCareSupport] : null;
    }

    public function setHomeCareSupport(?int $homeCareSupport): self
    {
        $this->homeCareSupport = $homeCareSupport;

        return $this;
    }

    public function getHomeCareSupportType(): ?int
    {
        return $this->homeCareSupportType;
    }

    public function getHomeCareSupportTypeToString(): ?string
    {
        return $this->homeCareSupportType ? self::CARE_SUPPORT[$this->homeCareSupportType] : null;
    }

    public function setHomeCareSupportType(?int $homeCareSupportType): self
    {
        $this->homeCareSupportType = $homeCareSupportType;

        return $this;
    }

    public function getFamilyBreakdown(): ?int
    {
        return $this->familyBreakdown;
    }

    public function getFamilyBreakdownToString(): ?string
    {
        return $this->familyBreakdown ? EvaluationChoices::YES_NO_PARTIAL[$this->familyBreakdown] : null;
    }

    public function setFamilyBreakdown(?int $familyBreakdown): self
    {
        $this->familyBreakdown = $familyBreakdown;

        return $this;
    }

    public function getFriendshipBreakdown(): ?int
    {
        return $this->friendshipBreakdown;
    }

    public function getFriendshipBreakdownToString(): ?string
    {
        return $this->friendshipBreakdown ? EvaluationChoices::YES_NO_PARTIAL[$this->friendshipBreakdown] : null;
    }

    public function setFriendshipBreakdown(?int $friendshipBreakdown): self
    {
        $this->friendshipBreakdown = $friendshipBreakdown;

        return $this;
    }

    public function getViolenceVictim(): ?int
    {
        return $this->violenceVictim;
    }

    public function getViolenceVictimToString(): ?string
    {
        return $this->violenceVictim ? EvaluationChoices::YES_NO[$this->violenceVictim] : null;
    }

    public function setViolenceVictim(?int $violenceVictim): self
    {
        $this->violenceVictim = $violenceVictim;

        return $this;
    }

    public function getDomViolenceVictim(): ?int
    {
        return $this->domViolenceVictim;
    }

    public function getDomViolenceVictimToString(): ?string
    {
        return $this->domViolenceVictim ? EvaluationChoices::YES_NO[$this->domViolenceVictim] : null;
    }

    public function setDomViolenceVictim(?int $domViolenceVictim): self
    {
        $this->domViolenceVictim = $domViolenceVictim;

        return $this;
    }

    public function getCommentEvalSocialPerson(): ?string
    {
        return $this->commentEvalSocialPerson;
    }

    public function setCommentEvalSocialPerson(?string $commentEvalSocialPerson): self
    {
        $this->commentEvalSocialPerson = $commentEvalSocialPerson;

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
