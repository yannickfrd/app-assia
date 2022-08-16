<?php

namespace App\Entity\Admin;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Admin\ExportModelRepository")
 */
class ExportModel
{
    use CreatedUpdatedEntityTrait;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="text", length=255, nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;
    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $share;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content ? \unserialize($this->content) : null;
    }

    public function setContent(?array $content): self
    {
        $this->content = \serialize($content);

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

    public function getShare(): ?bool
    {
        return $this->share;
    }

    public function setShare(?bool $share): self
    {
        $this->share = $share;

        return $this;
    }
}
