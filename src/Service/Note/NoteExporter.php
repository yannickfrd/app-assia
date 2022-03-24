<?php

namespace App\Service\Note;

use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportNoteSearch;
use App\Repository\Support\NoteRepository;
use App\Service\ExportPDF;
use App\Service\ExportWord;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Twig\Environment;

class NoteExporter
{
    private $noteRepo;
    private $exportWord;
    private $renderer;

    public function __construct(NoteRepository $noteRepo, ExportWord $exportWord, Environment $renderer)
    {
        $this->noteRepo = $noteRepo;
        $this->exportWord = $exportWord;
        $this->renderer = $renderer;
    }

    public function exportAll(SupportGroup $supportGroup, SupportNoteSearch $search): StreamedResponse
    {
        $notes = $this->noteRepo->findNotesOfSupport($supportGroup->getId(), $search);

        $content = '';
        foreach ($notes as $note) {
            $content .= '<p style="text-align:center"><strong style="font-size: 18px;">--- '.
            htmlspecialchars($note->getTitle(), \ENT_COMPAT | \ENT_HTML5).
                ' ---</strong><br/><small style="font-size: 12px; color: gray;">'.
            $note->getCreatedAt()->format('d/m/Y').'</small></p>'.
            $note->getContent().'<br/>';
        }

        $this->exportWord->createDocument(
            $content,
            'Notes '.$supportGroup->getHeader()->getFullname(),
            $supportGroup->getService()->getPole()->getLogoPath()
        );

        return $this->exportWord->download();
    }

    public function exportOne(Request $request, Note $note, SupportGroup $supportGroup): StreamedResponse
    {
        $export = 'note_export_word' === $request->attributes->get('_route') ? $this->exportWord : new ExportPDF();

        $content = $note->getContent();
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();
        $fullnameSupport = $supportGroup->getHeader()->getFullname();

        if ($export instanceof ExportPDF) {
            $content = $export->formatContent($content, $this->renderer, $note->getTitle(), $logoPath, $fullnameSupport);
        }

        $export->createDocument($content, $note->getTitle(), $logoPath, $fullnameSupport);

        return $export->download();
    }
}
