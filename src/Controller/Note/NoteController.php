<?php

declare(strict_types=1);

namespace App\Controller\Note;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\NoteSearch;
use App\Form\Model\Support\SupportNoteSearch;
use App\Form\Support\Note\NoteSearchType;
use App\Form\Support\Note\NoteType;
use App\Form\Support\Note\SupportNoteSearchType;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\PaymentRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Evaluation\EvaluationExporter;
use App\Service\Note\NoteExporter;
use App\Service\Note\NoteManager;
use App\Service\Note\NotePaginator;
use App\Service\SupportGroup\SupportCollections;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class NoteController extends AbstractController
{
    use ErrorMessageTrait;

    /**
     * @Route("/notes", name="note_index", methods="GET|POST")
     */
    public function index(Request $request, NotePaginator $notePaginator): Response
    {
        $form = $this->createForm(NoteSearchType::class, $search = new NoteSearch())
            ->handleRequest($request);

        return $this->render('app/note/note_index.html.twig', [
            'form' => $form->createView(),
            'notes' => $notePaginator->paginateNotes($request, $search),
        ]);
    }

    /**
     * @Route("/support/{id}/notes", name="support_note_index", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function supportNotesIndex(
        int $id,
        SupportManager $supportManager,
        SupportCollections $supportCollections,
        Request $request,
        NotePaginator $notePaginator,
        NoteExporter $noteExporter
    ): Response {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $formSearch = $this->createForm(SupportNoteSearchType::class, $search = new SupportNoteSearch(), [
            'service' => $supportGroup->getService(),
        ]);
        $formSearch->handleRequest($request);

        $note = (new Note())->setSupportGroup($supportGroup);

        $form = $this->createForm(NoteType::class, $note);

        if ($search->getExport()) {
            return $noteExporter->exportAll($supportGroup, $search);
        }

        return $this->render('app/note/support_note_index.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nb_total_notes' => $supportCollections->getNbNotes($supportGroup),
            'notes' => $notePaginator->paginateSupportNotes($supportGroup, $request, $search),
        ]);
    }

    /**
     * @Route("/support/{id}/note/new", name="support_note_new", methods="POST")
     */
    public function new(
        SupportGroup $supportGroup,
        Request $request,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
        TranslatorInterface $translator
    ): JsonResponse {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $note = (new Note())->setSupportGroup($supportGroup);

        $form = $this->createForm(NoteType::class, $note)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $note->setSupportGroup($supportGroup);

            $em->persist($note);
            $em->flush();

            NoteManager::deleteCacheItems($note);

            return $this->json([
                'action' => 'create',
                'alert' => 'success',
                'msg' => $translator->trans('note.created_successfully', ['%note_title%' => $note->getTitle()], 'app'),
                'note' => $normalizer->normalize($note, 'json', ['groups' => ['show_note', 'show_tag']]),
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/note/{id}/edit", name="note_edit", methods="POST")
     * @IsGranted("EDIT", subject="note")
     */
    public function edit(Note $note, Request $request, EntityManagerInterface $em,  NormalizerInterface $normalizer,
        TranslatorInterface $translator): JsonResponse
    {
        $form = $this->createForm(NoteType::class, $note)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            NoteManager::deleteCacheItems($note);

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => $translator->trans('note.updated_successfully', ['%note_title%' => $note->getTitle()], 'app'),
                'note' => $normalizer->normalize($note, 'json', ['groups' => ['show_note', 'show_tag']]),
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/note/{id}/delete", name="note_delete", methods="GET")
     * @IsGranted("DELETE", subject="note")
     */
    public function delete(Note $note, EntityManagerInterface $em, TranslatorInterface $translator): JsonResponse
    {
        $noteId = $note->getId();

        $em->remove($note);
        $em->flush();

        NoteManager::deleteCacheItems($note);

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $translator->trans('note.deleted_successfully', ['%note_title%' => $note->getTitle()], 'app'),
            'note' => ['id' => $noteId],
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

        return $noteExporter->exportOne($request, $note, $supportGroup);
    }

    /**
     * Générer une note à partir de la dernière évaluation sociale du suivi.
     *
     * @Route("/support/{id}/note/new_evaluation", name="support_note_new_evaluation", methods="GET")
     */
    public function generateEvaluationNote(
        int $id,
        SupportGroupRepository $supportGroupRepo,
        EvaluationExporter $evaluationExporter,
        EntityManagerInterface $em,
        PaymentRepository $paymentRepo
    ): Response {
        $supportGroup = $supportGroupRepo->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $payments = 1 === $supportGroup->getService()->getContribution() ? $paymentRepo->findPaymentsOfSupport($id) : null;

        $note = $evaluationExporter->createNote($supportGroup, $payments);

        if (!$note) {
            $this->addFlash('warning', "Il n'y a pas d'évaluation sociale créée pour ce suivi.");

            return $this->redirectToRoute('support_show', ['id' => $id]);
        }

        $em->persist($note);
        $em->flush();

        NoteManager::deleteCacheItems($note);

        return $this->redirectToRoute('support_note_index', [
            'id' => $id,
            'noteId' => $note->getId(),
        ]);
    }
}
