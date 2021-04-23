<?php

namespace App\Event\Note;

use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use Symfony\Contracts\EventDispatcher\Event;

class NoteEvent extends Event
{
    public const NAME = 'note.event';

    private $note;
    private $supportGroup;

    public function __construct(Note $note, SupportGroup $supportGroup = null)
    {
        $this->note = $note;
        $this->supportGroup = $supportGroup;
    }

    public function getNote(): Note
    {
        return $this->note;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup ?? $this->note->getSupportGroup();
    }
}
