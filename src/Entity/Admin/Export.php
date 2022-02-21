<?php

namespace App\Entity\Admin;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Admin\ExportRepository")
 */
class Export
{
    use CreatedUpdatedEntityTrait;

    public const STATUS_IN_PROGRESS = 0;
    public const STATUS_TERMINATE = 1;

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
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fileName;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $size;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $usedMemory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbResults;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getSize(): ?float
    {
        return $this->size;
    }

    public function setSize(?float $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getUsedMemory(): ?float
    {
        return $this->usedMemory;
    }

    public function setUsedMemory(?float $usedMemory): self
    {
        $this->usedMemory = $usedMemory;

        return $this;
    }

    public function getNbResults(): ?int
    {
        return $this->nbResults;
    }

    public function setNbResults(?int $nbResults): self
    {
        $this->nbResults = $nbResults;

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
}
