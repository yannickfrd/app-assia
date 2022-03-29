<?php

namespace App\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

abstract class AbstractFinance
{
    use SoftDeleteableEntity;

    public const OTHER = 1000;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="smallint")
     */
    protected $type;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $amount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $comment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    abstract public function getTypeToString(): ?string;

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): self
    {
        $this->amount = $amount;

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
}
