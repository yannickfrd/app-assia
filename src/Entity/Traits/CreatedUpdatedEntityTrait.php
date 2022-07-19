<?php

namespace App\Entity\Traits;

use App\Entity\Organization\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

trait CreatedUpdatedEntityTrait
{
    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("show_created_updated")
     */
    protected $createdAt;

    /**
     * @var User
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups("show_created_updated")
     */
    protected $createdBy;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="create", on="update")
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("show_created_updated")
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

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
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

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
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
