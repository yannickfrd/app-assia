<?php

namespace App\Form\Model\Support\Traits;

use Doctrine\Common\Collections\ArrayCollection;

trait NoteSearchTrait
{
    /**
     * @var string|null
     */
    private $content;

    /**
     * @var int|null
     */
    private $type;

    /**
     * @var int|null
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

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
