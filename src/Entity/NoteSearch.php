<?php

namespace App\Entity;

use App\Entity\Note;
use Symfony\Component\Validator\Constraints as Assert;

class NoteSearch
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

    public function getTypeList()
    {
        return Note::TYPE[$this->type];
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

    public function getStatusList()
    {
        return Note::STATUS[$this->status];
    }
}
