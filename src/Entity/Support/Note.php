<?php

namespace App\Entity\Support;

use App\Entity\Organization\Traits\TagTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\NoteRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Note
{
    use CreatedUpdatedEntityTrait;
    use TagTrait;
    use SoftDeleteableEntity;

    public const SERIALIZER_GROUPS = ['show_note', 'show_tag', 'show_support_group', 'show_person'];

    public const TYPE_NOTE = 1;
    public const TYPE_REPORT = 2;

    public const TYPE = [
        1 => 'Note',
        2 => 'Rapport social',
        97 => 'Autre',
    ];

    public const STATUS_DEFAULT = 1;

    public const STATUS = [
        1 => 'Brouillon',
        2 => 'Finalisé',
        3 => 'En attente validation',
        4 => 'Validé',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("show_note")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("show_note")
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups("show_note")
     */
    private $content;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("show_note")
     */
    private $type = self::TYPE_NOTE;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("show_note")
     */
    private $status = self::STATUS_DEFAULT;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="notes")
     */
    protected $createdBy; // NE PAS SUPPRIMER

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportGroup", inversedBy="notes")
     * @Groups("show_note")
     */
    private $supportGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportPerson", inversedBy="notes")
     */
    private $supportPerson;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * @ORM\PreFlush
     */
    public function preFlush()
    {
        if ($this->supportGroup) {
            $this->supportGroup->setUpdatedAt(new \DateTime());
        }
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @Groups("show_note")
     */
    public function getTypeToString(): ?string
    {
        return $this->type ? self::TYPE[$this->type] : null;
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

    /**
     * @Groups("show_note")
     */
    public function getStatusToString(): ?string
    {
        return $this->status ? self::STATUS[$this->status] : null;
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

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }

    public function getSupportPerson(): ?SupportPerson
    {
        return $this->supportPerson;
    }

    public function setSupportPerson(?SupportPerson $supportPerson): self
    {
        $this->supportPerson = $supportPerson;

        return $this;
    }

    /**
     * @Groups("show_note")
     */
    public function getUpdatedAtToString(string $format = 'd/m/Y à H:i'): string
    {
        return $this->updatedAt ? $this->updatedAt->format($format) : '';
    }

    /**
     * @Groups("show_note")
     */
    public function getUpdatedByToString(): string
    {
        return $this->updatedBy ? $this->updatedBy->getFullname() : '';
    }

    /**
     * @Groups("show_note")
     */
    public function getCreatedAtToString(string $format = 'd/m/Y'): string
    {
        return $this->createdAt ? $this->createdAt->format($format) : '';
    }
}
