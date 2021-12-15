<?php

namespace App\Entity\Evaluation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

/**
 * @ORM\Entity(repositoryClass=EvalHotelLifeGroupRepository::class)
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class EvalHotelLifeGroup
{
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $food;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $clothing;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $roomMaintenance;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $otherHotelLife;
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentHotelLife;

    /**
     * @ORM\OneToOne(targetEntity=EvaluationGroup::class, inversedBy="evalHotelLifeGroup", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $evaluationGroup;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFood(): ?string
    {
        return $this->food;
    }

    public function setFood(?string $food): self
    {
        $this->food = $food;

        return $this;
    }

    public function getClothing(): ?string
    {
        return $this->clothing;
    }

    public function setClothing(?string $clothing): self
    {
        $this->clothing = $clothing;

        return $this;
    }

    public function getRoomMaintenance(): ?string
    {
        return $this->roomMaintenance;
    }

    public function setRoomMaintenance(?string $roomMaintenance): self
    {
        $this->roomMaintenance = $roomMaintenance;

        return $this;
    }

    public function getOtherHotelLife(): ?string
    {
        return $this->otherHotelLife;
    }

    public function setOtherHotelLife(?string $otherHotelLife): self
    {
        $this->otherHotelLife = $otherHotelLife;

        return $this;
    }

    public function getCommentHotelLife(): ?string
    {
        return $this->commentHotelLife;
    }

    public function setCommentHotelLife(?string $commentHotelLife): self
    {
        $this->commentHotelLife = $commentHotelLife;

        return $this;
    }

    public function getEvaluationGroup(): ?EvaluationGroup
    {
        return $this->evaluationGroup;
    }

    public function setEvaluationGroup(EvaluationGroup $evaluationGroup): self
    {
        $this->evaluationGroup = $evaluationGroup;

        return $this;
    }
}
