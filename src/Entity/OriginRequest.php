<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OriginRequestRepository")
 */
class OriginRequest
{
    public const RESULT_PRE_ADMISSION = [
        1 => 'En cours',
        2 => 'Admission',
        3 => 'Refus du service',
        4 => 'Refus de la personne',
        5 => 'Refus autre',
        97 => 'Autre',
        98 => 'Non concerné',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $orientationDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $preAdmissionDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $resulPreAdmission;

    /**
     * @Groups("export")
     */
    private $resulPreAdmissionToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $decisionDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SupportGroup", inversedBy="originRequest", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", inversedBy="originRequests")
     * @ORM\JoinColumn(nullable=true)
     * @Groups("export")
     */
    private $organization;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("export")
     */
    private $organizationComment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrientationDate(): ?\DateTimeInterface
    {
        return $this->orientationDate;
    }

    public function setOrientationDate(?\DateTimeInterface $orientationDate): self
    {
        $this->orientationDate = $orientationDate;

        return $this;
    }

    public function getPreAdmissionDate(): ?\DateTimeInterface
    {
        return $this->preAdmissionDate;
    }

    public function setPreAdmissionDate(?\DateTimeInterface $preAdmissionDate): self
    {
        $this->preAdmissionDate = $preAdmissionDate;

        return $this;
    }

    public function getResulPreAdmission(): ?int
    {
        return $this->resulPreAdmission;
    }

    public function getResulPreAdmissionToString(): ?string
    {
        return $this->resulPreAdmission ? self::RESULT_PRE_ADMISSION[$this->resulPreAdmission] : null;
    }

    public function setResulPreAdmission(?int $resulPreAdmission): self
    {
        $this->resulPreAdmission = $resulPreAdmission;

        return $this;
    }

    public function getDecisionDate(): ?\DateTimeInterface
    {
        return $this->decisionDate;
    }

    public function setDecisionDate(?\DateTimeInterface $decisionDate): self
    {
        $this->decisionDate = $decisionDate;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getOrganizationComment(): ?string
    {
        return $this->organizationComment;
    }

    public function setOrganizationComment(?string $organizationComment): self
    {
        $this->organizationComment = $organizationComment;

        return $this;
    }
}
