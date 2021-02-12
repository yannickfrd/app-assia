<?php

namespace App\Form\Model\Support;

use App\Form\Model\Support\Traits\NoteSearchTrait;

class SupportNoteSearch
{
    use NoteSearchTrait;

    /** @var int|null */
    private $noteId;

    public function getNoteId(): ?int
    {
        return $this->noteId;
    }

    public function setNoteId(?int $noteId): self
    {
        $this->noteId = $noteId;

        return $this;
    }
}
