<?php

namespace App\Service\Note;

use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportNoteSearch;
use App\Repository\Support\NoteRepository;
use App\Service\ExportWord;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportNotes
{
    private $repoNote;
    private $exportWord;

    public function __construct(NoteRepository $repoNote, ExportWord $exportWord)
    {
        $this->repoNote = $repoNote;
        $this->exportWord = $exportWord;
    }

    public function send(SupportGroup $supportGroup, SupportNoteSearch $search): StreamedResponse
    {
        $notes = $this->repoNote->findNotesOfSupport($supportGroup->getId(), $search);

        $content = '';
        foreach ($notes as $note) {
            $content .= '<p style="text-align:center"><strong style="font-size: 18px;">--- '.
            $note->getTitle().' ---</strong><br/><small style="font-size: 12px; color: gray;">'.
            $note->getCreatedAt()->format('d/m/Y').'</small></p>'.
            $note->getContent().'<br/>';
        }

        $this->exportWord->createDocument(
            $content,
            'Notes '.$supportGroup->getHeader()->getFullname(),
            $supportGroup->getService()->getPole()->getLogoPath());

        return $this->exportWord->download();
    }
}
