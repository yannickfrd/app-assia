<?php

namespace App\Entity\Traits;

use App\Entity\Organization\User;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait CreatedUpdatedEntityTrait
{
    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("view")
     */
    protected $createdAt;

    /**
     * @var User
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    protected $createdBy;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create", on="update")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("view")
     */
    protected $updatedAt;

    /**
     * @var User
     * @Gedmo\Blameable(on="create", on="update")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
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
