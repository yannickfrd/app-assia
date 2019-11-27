<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SitAdmRepository")
 */
class SitAdm
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
        98 => "Autre",
        99 => "Non renseigné"
    ];

    public const RIGHT_TO_RESIDE = [
        1 => "Débouté du droit d'asile",
        2 => "Demandeur d'asile",
        3 => "Réfugié",
        98 => "Autre",
        99 => "Non renseigné"
    ];

    public const SOCIAL_SECURITY = [
        5 => "AME",
        3 => "CMU",
        4 => "CMU complémentaire",
        2 => "Mutuelle",
        1 => "Régime général",
        98 => "Autre régime",
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
    private $rightReside;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $applResidPermit;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDateValidPermit;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $renewalDatePermit;

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
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $rightSocialSecu;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $socialSecu;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $socialSecuOffice;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentSitAdm;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportPers", inversedBy="sitAdm", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportPers;

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


    public function getApplResidPermit(): ?int
    {
        return $this->applResidPermit;
    }

    public function setApplResidPermit(?int $applResidPermit): self
    {
        $this->applResidPermit = $applResidPermit;

        return $this;
    }

    public function getEndDateValidPermit(): ?\DateTimeInterface
    {
        return $this->endDateValidPermit;
    }

    public function setEndDateValidPermit(?\DateTimeInterface $endDateValidPermit): self
    {
        $this->endDateValidPermit = $endDateValidPermit;

        return $this;
    }

    public function getRenewalDatePermit(): ?\DateTimeInterface
    {
        return $this->renewalDatePermit;
    }

    public function setRenewalDatePermit(?\DateTimeInterface $renewalDatePermit): self
    {
        $this->renewalDatePermit = $renewalDatePermit;

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

    public function getRightSocialSecu(): ?int
    {
        return $this->rightSocialSecu;
    }

    public function setRightSocialSecu(?int $rightSocialSecu): self
    {
        $this->rightSocialSecu = $rightSocialSecu;

        return $this;
    }

    public function getSocialSecu(): ?int
    {
        return $this->socialSecu;
    }

    public function setSocialSecu(?int $socialSecu): self
    {
        $this->socialSecu = $socialSecu;

        return $this;
    }

    public function getSocialSecuList()
    {
        return self::SOCIAL_SECURITY[$this->socialSecu];
    }

    public function getSocialSecuOffice(): ?string
    {
        return $this->socialSecuOffice;
    }

    public function setSocialSecuOffice(?string $socialSecuOffice): self
    {
        $this->socialSecuOffice = $socialSecuOffice;

        return $this;
    }

    public function getCommentSitAdm(): ?string
    {
        return $this->commentSitAdm;
    }

    public function setCommentSitAdm(?string $commentSitAdm): self
    {
        $this->commentSitAdm = $commentSitAdm;

        return $this;
    }

    public function getSupportPers(): ?SupportPers
    {
        return $this->supportPers;
    }

    public function setSupportPers(SupportPers $supportPers): self
    {
        $this->supportPers = $supportPers;

        return $this;
    }
}
