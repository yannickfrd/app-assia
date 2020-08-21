<?php

namespace App\Entity;

use App\Repository\EvalHotelLifeGroupRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EvalHotelLifeGroupRepository::class)
 */
class EvalHotelLifeGroup
{
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
    private $otherCommentHotelLife;

    /**
     * @ORM\OneToOne(targetEntity=EvaluationGroup::class, inversedBy="evalHotelLifeGroup", cascade={"persist", "remove"})
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

    public function getOtherCommentHotelLife(): ?string
    {
        return $this->otherCommentHotelLife;
    }

    public function setOtherCommentHotelLife(?string $otherCommentHotelLife): self
    {
        $this->otherCommentHotelLife = $otherCommentHotelLife;

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
