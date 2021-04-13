<?php

namespace App\Entity\Support;

use App\Entity\Organization\User;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\RdvRepository")
 */
class Rdv
{
    use CreatedUpdatedEntityTrait;

    public const STATUS = [
        1 => 'Présent',
        2 => 'Absent',
        3 => 'Annulé',
        99 => 'Non évalué',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     */
    private $start;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     */
    private $end;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="rdvs")
     */
    private $createdBy; // NE PAS SUPPRIMER

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportGroup", inversedBy="rdvs", fetch="EXTRA_LAZY")
     */
    private $supportGroup;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="rdvs2")
     */
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

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

    public function getStatusToString(): ?string
    {
        return $this->status ? self::STATUS[$this->status] : null;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
