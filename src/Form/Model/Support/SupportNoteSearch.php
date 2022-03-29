<?php

namespace App\Form\Model\Support;

use App\Form\Model\Support\Traits\NoteSearchTrait;

class SupportNoteSearch
{
    use NoteSearchTrait;

    /** @var int|null */
    private $noteId;

    /** @var bool */
    private $export;

    /** @var bool */
    private $disable = false;

    public function getNoteId(): ?int
    {
        return $this->noteId;
    }

    public function setNoteId(?int $noteId): self
    {
        $this->noteId = $noteId;

        return $this;
    }

    public function getExport(): ?bool
    {
        return $this->export;
    }

    public function setExport(bool $export): self
    {
        $this->export = $export;

        return $this;
    }

    public function getDisable(): bool
    {
        return $this->disable;
    }

    public function setDisable(bool $disable): self
    {
        $this->disable = $disable;

        return $this;
    }

}
