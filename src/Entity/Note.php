<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NoteRepository")
 */
class Note
{
    use CreatedUpdatedEntityTrait;

    public const TYPE = [
        1 => 'Note',
        2 => 'Rapport social',
        3 => 'Autre',
    ];

    public const TYPE_DEFAULT = 1;

    public const STATUS = [
        1 => 'Brouillon',
        2 => 'Finalisé',
        3 => 'En attente validation',
        4 => 'Validé',
    ];

    public const STATUS_DEFAULT = 1;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $type = self::TYPE_DEFAULT;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status = self::STATUS_DEFAULT;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notes")
     */
    private $createdBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportGroup", inversedBy="notes")
     */
    private $supportGroup;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportPerson", inversedBy="notes")
     */
    private $supportPerson;

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

    public function getTypeToString(): ?string
    {
        return self::TYPE[$this->type];
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
        return self::STATUS[$this->status];
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
}
