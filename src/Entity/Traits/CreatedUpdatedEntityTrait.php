<?php

namespace App\Entity\Traits;

use App\Entity\Organization\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait CreatedUpdatedEntityTrait
{
    /**
     * @var \DateTimeInterface
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"show_created_updated", "view", "show_rdv"})
     */
    protected $createdAt;

    /**
     * @var User
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups("show_created_updated", "show_rdv")
     */
    protected $createdBy;

    /**
     * @var \DateTimeInterface
     * @Gedmo\Timestampable(on="create", on="update")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"show_created_updated", "view", "show_rdv"})
     */
    protected $updatedAt;

    /**
     * @var User
     * @Gedmo\Blameable(on="create", on="update")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups("show_created_updated")
     */
    protected $updatedBy;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }
}
