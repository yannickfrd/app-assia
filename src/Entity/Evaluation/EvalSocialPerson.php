<?php

namespace App\Entity\Evaluation;

use App\Form\Utils\Choices;
use Doctrine\ORM\Mapping as ORM;
use SebastianBergmann\CodeCoverage\Report\Xml\Method;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Evaluation\EvalSocialPersonRepository")
 */
class EvalSocialPerson
{
    public const SOCIAL_SECURITY = [
        6 => 'ACS',
        5 => 'AME',
        4 => 'CSS (ex-CMU-C)',
        3 => 'PUMA (ex-CMU)',
        1 => 'Régime général',
        2 => 'Régime général et mutuelle',
        97 => 'Autre régime',
        99 => 'Non renseignée',
    ];

    public const CARE_SUPPORT = [
        1 => 'Infirmière/er à domicile',
        2 => 'PCH',
        3 => 'SAMSAH',
        4 => 'SAVS',
        97 => 'Autre',
        99 => 'Non renseignée',
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
        99 => 'Non renseignée',
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

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialSecurity;

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
    private $childWelfareBackground;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $aseFollowUp;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $aseMeasureType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $aseComment;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $familyBreakdown;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $friendshipBreakdown;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $healthProblem;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $physicalHealthProblem;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $mentalHealthProblem;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $addictionProblem;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $wheelchair;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $reducedMobility;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $medicalFollowUp;

    /**
     * @ORM\Column(name="care_support", type="smallint", nullable=true)
     */
    private $homeCareSupport;

    /**
     * @ORM\Column(name="care_support_type", type="smallint", nullable=true)
     */
    private $homeCareSupportType;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $violenceVictim;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $domViolenceVictim;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalSocialPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvaluationPerson", inversedBy="evalSocialPerson", cascade={"persist", "remove"})
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

    /**
     * @Groups("export")
     */
    public function getRightSocialSecurityToString(): ?string
    {
        return $this->rightSocialSecurity ? Choices::YES_NO_IN_PROGRESS[$this->rightSocialSecurity] : null;
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

    /**
     * @Groups("export")
     */
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

    /**
     * @Groups("export")
     */
    public function getChildWelfareBackgroundToString(): ?string
    {
        return $this->childWelfareBackground ? Choices::YES_NO[$this->childWelfareBackground] : null;
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

    /**
     * @Groups("export")
     */
    public function getAseFollowUpToString(): ?string
    {
        return $this->aseFollowUp ? Choices::YES_NO[$this->aseFollowUp] : null;
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

    /**
     * @Groups("export")
     */
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

    /**
     * @Groups("export")
     */
    public function getHealthProblemToString(): ?string
    {
        return $this->healthProblem ? Choices::YES_NO[$this->healthProblem] : null;
    }

    public function setHealthProblem(?int $healthProblem): self
    {
        $this->healthProblem = $healthProblem;

        return $this;
    }

    public function getHealthProblemsType(): array
    {
        $array = [];

        foreach (self::HEALTH_PROBLEMS_TYPE as $key => $value) {
            $method = 'get'.ucfirst($key);
            if (Choices::YES === $this->$method()) {
                $array[] = $value;
            }
        }

        return $array;
    }

    public function getPhysicalHealthProblem(): ?int
    {
        return $this->physicalHealthProblem;
    }

    /**
     * @Groups("export")
     */
    public function getPhysicalHealthProblemToString(): ?string
    {
        return $this->physicalHealthProblem ? Choices::YES_NO_BOOLEAN[$this->physicalHealthProblem] : null;
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

    /**
     * @Groups("export")
     */
    public function getMentalHealthProblemToString(): ?string
    {
        return $this->mentalHealthProblem ? Choices::YES_NO_BOOLEAN[$this->mentalHealthProblem] : null;
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

    /**
     * @Groups("export")
     */
    public function getAddictionProblemToString(): ?string
    {
        return $this->addictionProblem ? Choices::YES_NO_BOOLEAN[$this->addictionProblem] : null;
    }

    public function setAddictionProblem(?int $addictionProblem): self
    {
        $this->addictionProblem = $addictionProblem;

        return $this;
    }

    public function getMedicalFollowUp(): ?int
    {
        return $this->medicalFollowUp;
    }

    /**
     * @Groups("export")
     */
    public function getMedicalFollowUpToString(): ?string
    {
        return $this->medicalFollowUp ? Choices::YES_NO_IN_PROGRESS[$this->medicalFollowUp] : null;
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

    /**
     * @Groups("export")
     */
    public function getHomeCareSupportToString(): ?string
    {
        return $this->homeCareSupport ? Choices::YES_NO_IN_PROGRESS[$this->homeCareSupport] : null;
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

    /**
     * @Groups("export")
     */
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

    /**
     * @Groups("export")
     */
    public function getFamilyBreakdownToString(): ?string
    {
        return $this->familyBreakdown ? Choices::YES_NO_PARTIAL[$this->familyBreakdown] : null;
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

    /**
     * @Groups("export")
     */
    public function getFriendshipBreakdownToString(): ?string
    {
        return $this->friendshipBreakdown ? Choices::YES_NO_PARTIAL[$this->friendshipBreakdown] : null;
    }

    public function setFriendshipBreakdown(?int $friendshipBreakdown): self
    {
        $this->friendshipBreakdown = $friendshipBreakdown;

        return $this;
    }

    public function getWheelchair(): ?int
    {
        return $this->wheelchair;
    }

    /**
     * @Groups("export")
     */
    public function getWheelchairToString(): ?string
    {
        return $this->wheelchair ? Choices::YES_NO_BOOLEAN[$this->wheelchair] : null;
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

    /**
     * @Groups("export")
     */
    public function getReducedMobilityToString(): ?string
    {
        return $this->reducedMobility ? Choices::YES_NO_BOOLEAN[$this->reducedMobility] : null;
    }

    public function setReducedMobility(?int $reducedMobility): self
    {
        $this->reducedMobility = $reducedMobility;

        return $this;
    }

    public function getViolenceVictim(): ?int
    {
        return $this->violenceVictim;
    }

    /**
     * @Groups("export")
     */
    public function getViolenceVictimToString(): ?string
    {
        return $this->violenceVictim ? Choices::YES_NO[$this->violenceVictim] : null;
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

    /**
     * @Groups("export")
     */
    public function getDomViolenceVictimToString(): ?string
    {
        return $this->domViolenceVictim ? Choices::YES_NO[$this->domViolenceVictim] : null;
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
