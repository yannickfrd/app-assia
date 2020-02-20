<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EvalSocialPersonRepository")
 */
class EvalSocialPerson
{
    public const SOCIAL_SECURITY = [
        6 => "ACS",
        5 => "AME",
        4 => "CSC (ex-CMU-C)",
        3 => "PUMA (ex-CMU)",
        2 => "Mutuelle",
        1 => "Régime général",
        97 => "Autre régime",
        99 => "Non renseignée"
    ];

    public const CARE_SUPPORT = [
        1 => "Infirmier à domicile",
        2 => "PCH",
        3 => "SAMSAH",
        4 => "SAVS",
        97 => "Autre",
        99 => "Non renseignée"
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
    private $careSupport;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $careSupportType;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEvalSocialPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\EvaluationPerson", inversedBy="evalSocialPerson", cascade={"persist", "remove"})
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

    public function setRightSocialSecurity(?int $rightSocialSecurity): self
    {
        $this->rightSocialSecurity = $rightSocialSecurity;

        return $this;
    }

    public function getSocialSecurity(): ?int
    {
        return $this->socialSecurity;
    }

    public function setSocialSecurity(?int $socialSecurity): self
    {
        $this->socialSecurity = $socialSecurity;

        return $this;
    }

    public function getSocialSecurityList()
    {
        return self::SOCIAL_SECURITY[$this->socialSecurity];
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

    public function setChildWelfareBackground(?int $childWelfareBackground): self
    {
        $this->childWelfareBackground = $childWelfareBackground;

        return $this;
    }

    public function getFamilyBreakdown(): ?int
    {
        return $this->familyBreakdown;
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

    public function setFriendshipBreakdown(?int $friendshipBreakdown): self
    {
        $this->friendshipBreakdown = $friendshipBreakdown;

        return $this;
    }

    public function getHealthProblem(): ?int
    {
        return $this->healthProblem;
    }

    public function setHealthProblem(?int $healthProblem): self
    {
        $this->healthProblem = $healthProblem;

        return $this;
    }

    public function getPhysicalHealthProblem(): ?int
    {
        return $this->physicalHealthProblem;
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

    public function setMentalHealthProblem(?int $mentalHealthProblem): self
    {
        $this->mentalHealthProblem = $mentalHealthProblem;

        return $this;
    }

    public function getAddictionProblem(): ?int
    {
        return $this->addictionProblem;
    }

    public function setAddictionProblem(?int $addictionProblem): self
    {
        $this->addictionProblem = $addictionProblem;

        return $this;
    }

    public function getCareSupport(): ?int
    {
        return $this->careSupport;
    }

    public function setCareSupport(?int $careSupport): self
    {
        $this->careSupport = $careSupport;

        return $this;
    }

    public function getCareSupportType(): ?int
    {
        return $this->careSupportType;
    }

    public function setCareSupportType(?int $careSupportType): self
    {
        $this->careSupportType = $careSupportType;

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
