<?php

namespace App\Entity\Support;

use App\Entity\Organization\TagTrait;
use App\Entity\Organization\User;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\RdvRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Rdv
{
    use TagTrait;
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    public const STATUS = [
        1 => 'Présent',
        2 => 'Absent',
        3 => 'Annulé',
        99 => 'Non renseigné',
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

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $googleEventId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $outlookEventId;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

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

    public function getGoogleEventId(): ?string
    {
        return $this->googleEventId;
    }

    public function setGoogleEventId(?string $googleEventId): self
    {
        $this->googleEventId = $googleEventId;

        return $this;
    }

    public function getOutlookEventId(): ?string
    {
        return $this->outlookEventId;
    }

    public function setOutlookEventId(?string $outlookEventId): self
    {
        $this->outlookEventId = $outlookEventId;

        return $this;
    }
}
