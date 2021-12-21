<?php

namespace App\Form\Model\Support;

use App\Form\Model\Support\Traits\NoteSearchTrait;
use Doctrine\Common\Collections\ArrayCollection;

class SupportNoteSearch
{
    use NoteSearchTrait;

    /** @var int|null */
    private $noteId;

    /** @var bool */
    private $export;

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
}
