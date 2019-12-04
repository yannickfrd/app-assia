<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SupportPersonRepository")
 */
class SupportPerson
{
    public const STATUS = [
        1 => "À venir",
        2 => "En cours",
        3 => "Suspendu",
        4 => "Terminé",
        5 => "Autre"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotNull(message="La date de début ne doit pas être vide.")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="Le statut doit être renseigné.")
     * @Assert\Range(min = 1, max = 5, minMessage="Le statut doit être renseigné.",  maxMessage="Le statut doit être renseigné.")
     */
    private $status;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Person", inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportGroup", inversedBy="supportPerson")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitFamilyPerson", mappedBy="supportPerson", cascade={"persist", "remove"})
     */
    private $sitFamilyPerson;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitProf", mappedBy="supportPerson", cascade={"persist", "remove"})
     */
    private $sitProf;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitAdm", mappedBy="supportPerson", cascade={"persist", "remove"})
     */
    private $sitAdm;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitBudget", mappedBy="supportPerson", cascade={"persist", "remove"})
     */
    private $sitBudget;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getStatusType()
    {
        return self::STATUS[$this->status];
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }

    public function getSitFamilyPerson(): ?SitFamilyPerson
    {
        return $this->sitFamilyPerson;
    }

    public function setSitFamilyPerson(SitFamilyPerson $sitFamilyPerson): self
    {
        $this->sitFamilyPerson = $sitFamilyPerson;

        // set the owning side of the relation if necessary
        if ($this !== $sitFamilyPerson->getSupportPerson()) {
            $sitFamilyPerson->setSupportPerson($this);
        }

        return $this;
    }

    public function getSitProf(): ?SitProf
    {
        return $this->sitProf;
    }

    public function setSitProf(SitProf $sitProf): self
    {
        $this->sitProf = $sitProf;

        // set the owning side of the relation if necessary
        if ($this !== $sitProf->getSupportPerson()) {
            $sitProf->setSupportPerson($this);
        }

        return $this;
    }

    public function getSitAdm(): ?SitAdm
    {
        return $this->sitAdm;
    }

    public function setSitAdm(SitAdm $sitAdm): self
    {
        $this->sitAdm = $sitAdm;

        // set the owning side of the relation if necessary
        if ($this !== $sitAdm->getSupportPerson()) {
            $sitAdm->setSupportPerson($this);
        }

        return $this;
    }

    public function getSitBudget(): ?SitBudget
    {
        return $this->sitBudget;
    }

    public function setSitBudget(SitBudget $sitBudget): self
    {
        $this->sitBudget = $sitBudget;

        // set the owning side of the relation if necessary
        if ($this !== $sitBudget->getSupportPerson()) {
            $sitBudget->setSupportPerson($this);
        }

        return $this;
    }
}
