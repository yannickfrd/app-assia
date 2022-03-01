<?php

namespace App\Form\Model\Support\Traits;

use Doctrine\Common\Collections\ArrayCollection;

trait RdvSearchTrait
{
    /**
     * @var string|null
     */
    private $title;

    /**
     * @var array
     */
    private $status;

    /**
     * @var ArrayCollection|null
     */
    protected $tags;

    public function getTags(): ?ArrayCollection
    {
        return $this->tags;
    }

    public function setTags(?ArrayCollection $tags): self
    {
        $this->tags = $tags;

        return $this;
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

    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function setStatus(?array $status): self
    {
        $this->status = $status;

        return $this;
    }
}
