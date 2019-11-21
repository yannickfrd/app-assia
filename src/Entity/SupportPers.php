<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SupportPersRepository")
 */
class SupportPers
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
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportGrp", inversedBy="supportPers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGrp;

    // /**
    //  * @ORM\OneToOne(targetEntity="App\Entity\SitFamilyPers", mappedBy="supportPers", cascade={"persist", "remove"})
    //  */
    // private $sitFamilyPers;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\SitProf", mappedBy="supportPers", cascade={"persist", "remove"})
     */
    private $sitProf;

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

    public function getSupportGrp(): ?SupportGrp
    {
        return $this->supportGrp;
    }

    public function setSupportGrp(?SupportGrp $supportGrp): self
    {
        $this->supportGrp = $supportGrp;

        return $this;
    }

    // public function getSitFamilyPers(): ?SitFamilyPers
    // {
    //     return $this->sitFamilyPers;
    // }

    // public function setSitFamilyPers(SitFamilyPers $sitFamilyPers): self
    // {
    //     $this->sitFamilyPers = $sitFamilyPers;

    //     // set the owning side of the relation if necessary
    //     if ($this !== $sitFamilyPers->getSupportPers()) {
    //         $sitFamilyPers->setSupportPers($this);
    //     }

    //     return $this;
    // }

    public function getSitProf(): ?SitProf
    {
        return $this->sitProf;
    }

    public function setSitProf(SitProf $sitProf): self
    {
        $this->sitProf = $sitProf;

        // set the owning side of the relation if necessary
        if ($this !== $sitProf->getSupportPers()) {
            $sitProf->setSupportPers($this);
        }

        return $this;
    }
}
