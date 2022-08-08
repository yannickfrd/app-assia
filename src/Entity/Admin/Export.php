<?php

namespace App\Entity\Admin;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Admin\ExportRepository")
 */
class Export
{
    use CreatedUpdatedEntityTrait;

    public const STATUS_IN_PROGRESS = 0;
    public const STATUS_TERMINATE = 1;
    public const STATUS_FAILED = 2;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("show_export")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("show_export")
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("show_export")
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("show_export")
     */
    private $fileName;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("show_export")
     */
    private $size;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups("show_export")
     */
    private $usedMemory;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("show_export")
     */
    private $nbResults;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("show_export")
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

    /** @Groups("show_export") */
    public function getSizeKo(): ?string
    {
        if (null === $this->size) {
            return null;
        }

        $roundedSize = round($this->size / 1_000);

        return number_format($roundedSize, 0, ',', "\xc2\xa0")."\xc2\xa0Ko";
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

    /** @Groups("show_export") */
    public function getDelay(): ?string
    {
        return $this->createdAt ? $this->createdAt->diff($this->updatedAt)->format('%im%ss') : null;
    }

    /** @Groups("show_export") */
    public function getCreatedAtToString(string $format = 'd/m/Y H:i'): string
    {
        return $this->createdAt ? $this->createdAt->format($format) : '';
    }
}
