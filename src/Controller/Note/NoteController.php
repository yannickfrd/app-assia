<?php

namespace App\Controller\Note;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Event\Note\NoteEvent;
use App\Form\Model\Support\NoteSearch;
use App\Form\Model\Support\SupportNoteSearch;
use App\Form\Support\Note\NoteSearchType;
use App\Form\Support\Note\NoteType;
use App\Form\Support\Note\SupportNoteSearchType;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Evaluation\EvaluationExporter;
use App\Service\Note\NoteExporter;
use App\Service\Note\NotePaginator;
use App\Service\SupportGroup\SupportCollections;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NoteController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Liste des notes.
     *
     * @Route("/notes", name="notes", methods="GET|POST")
     */
    public function listNotes(Request $request, NotePaginator $notePaginator): Response
    {
        $form = $this->createForm(NoteSearchType::class, $search = new NoteSearch())
            ->handleRequest($request);

        return $this->render('app/note/listNotes.html.twig', [
            'form' => $form->createView(),
            'notes' => $notePaginator->paginateNotes($request, $search),
        ]);
    }

    /**
     * Liste des notes du suivi social.
     *
     * @Route("/support/{id}/notes", name="support_notes", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function listSupportNotes(
        int $id,
        SupportManager $supportManager,
        SupportCollections $supportCollections,
        Request $request,
        NotePaginator $notePaginator,
        NoteExporter $noteExporter
    ): Response {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportNoteSearchType::class, $search = new SupportNoteSearch())
            ->handleRequest($request);

        $form = $this->createForm(NoteType::class, new Note());

        if ($search->getExport()) {
            return $noteExporter->exportAllNotes($supportGroup, $search);
        }

        return $this->render('app/note/supportNotes.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nbTotalNotes' => $supportCollections->getNbNotes($supportGroup),
            'notes' => $notePaginator->paginateSupportNotes($supportGroup, $request, $search),
        ]);
    }

    /**
     * Nouvelle note.
     *
     * @Route("/support/{id}/note/new", name="support_note_new", methods="POST")
     */
    public function createSupportNote(SupportGroup $supportGroup, Request $request, EventDispatcherInterface $dispatcher): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(NoteType::class, $note = new Note())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setSupportGroup($supportGroup);

            $this->manager->persist($note);
            $this->manager->flush();

            $dispatcher->dispatch(new NoteEvent($note, $supportGroup), 'note.after_create');

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => 'La note sociale est enregistrée.',
                'data' => [
                    'noteId' => $note->getId(),
                    'type' => $note->getTypeToString(),
                    'status' => $note->getStatusToString(),
                    'editInfo' => '| Créé le '.$note->getCreatedAt()->format('d/m/Y à H:i').' par '.$note->getCreatedBy()->getFullname(),
                ],
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Modification d'une note.
     *
     * @Route("/note/{id}/edit", name="note_edit", methods="POST")
     * @IsGranted("EDIT", subject="note")
     */
    public function editNote(Note $note, Request $request, EventDispatcherInterface $dispatcher): Response
    {
        $form = $this->createForm(NoteType::class, $note)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $dispatcher->dispatch(new NoteEvent($note), 'note.after_update');

            return $this->json([
            'action' => 'update',
            'alert' => 'success',
            'msg' => 'La note sociale est modifiée.',
            'data' => [
                'noteId' => $note->getId(),
                'type' => $note->getTypeToString(),
                'status' => $note->getStatusToString(),
                'editInfo' => '(modifié le '.$note->getUpdatedAt()->format('d/m/Y à H:i').' par '.$note->getUpdatedBy()->getFullname().')',
                ],
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Supprime la note.
     *
     * @Route("/note/{id}/delete", name="note_delete", methods="GET")
     * @IsGranted("DELETE", subject="note")
     */
    public function deleteNote(Note $note, EventDispatcherInterface $dispatcher): Response
    {
        $this->manager->remove($note);
        $this->manager->flush();

        $dispatcher->dispatch(new NoteEvent($note), 'note.after_update');

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'La note sociale est supprimée.',
        ]);
    }

    /**
     * Export de la note au format Word ou PDF.
     *
     * @Route("/note/{id}/export/word", name="note_export_word", methods="GET")
     * @Route("/note/{id}/export/pdf", name="note_export_pdf", methods="GET")
     */
    public function export(int $id, NoteRepository $noteRepo, Request $request, NoteExporter $noteExporter): Response
    {
        if (null === $note = $noteRepo->findNote($id)) {
            throw $this->createAccessDeniedException();
        }

        $supportGroup = $note->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        return $noteExporter->exportOneNote($request, $note, $supportGroup);
    }

    /**
     * Générer une note à partir de la dernière évaluation sociale du suivi.
     *
     * @Route("/support/{id}/note/new_evaluation", name="support_note_new_evaluation", methods="GET")
     */
    public function generateNoteEvaluation(
        int $id,
        SupportGroupRepository $supportGroupRepo,
        EvaluationExporter $evaluationExporter,
        EventDispatcherInterface $dispatcher
    ): Response {
        $supportGroup = $supportGroupRepo->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $note = $evaluationExporter->createNote($supportGroup);

        if (!$note) {
            $this->addFlash('warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');

            return $this->redirectToRoute('support_view', ['id' => $id]);
        }

        $this->manager->persist($note);
        $this->manager->flush();

        $dispatcher->dispatch(new NoteEvent($note), 'note.after_create');

        return $this->redirectToRoute('support_notes', [
            'id' => $id,
            'noteId' => $note->getId(),
        ]);
    }
}
