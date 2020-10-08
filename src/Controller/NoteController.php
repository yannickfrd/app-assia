<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Note;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Form\Model\NoteSearch;
use App\Form\Model\SupportNoteSearch;
use App\Form\Note\NoteSearchType;
use App\Form\Note\NoteType;
use App\Form\Note\SupportNoteSearchType;
use App\Repository\EvaluationGroupRepository;
use App\Repository\NoteRepository;
use App\Repository\RdvRepository;
use App\Repository\SupportGroupRepository;
use App\Security\CurrentUserService;
use App\Service\ExportPDF;
use App\Service\ExportWord;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportGroupService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class NoteController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, NoteRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des notes.
     *
     * @Route("notes", name="notes", methods="GET|POST")
     */
    public function listNotes(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $search = new NoteSearch();
        if ($this->getUser()->getStatus() == User::STATUS_SOCIAL_WORKER) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = ($this->createForm(NoteSearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/note/listNotes.html.twig', [
            'form' => $form->createView(),
            'notes' => $pagination->paginate($this->repo->findAllNotesQuery($search, $currentUser),
                $request,
                10) ?? null,
        ]);
    }

    /**
     * Liste des notes du suivi social.
     *
     * @Route("support/{id}/notes", name="support_notes", methods="GET|POST")
     *
     * @param int $id // SupportGroup
     */
    public function listSupportNotes(int $id, SupportGroupService $supportGroupService, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $supportGroupService->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $search = new SupportNoteSearch();

        $formSearch = $this->createForm(SupportNoteSearchType::class, $search)
            ->handleRequest($request);

        $form = $this->createForm(NoteType::class, new Note());

        return $this->render('app/note/supportNotes.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nbTotalNotes' => $request->query->count() ? $this->repo->count(['supportGroup' => $supportGroup]) : null,
            'notes' => $pagination->paginate($this->repo->findAllNotesFromSupportQuery($supportGroup->getId(), $search), $request, 10) ?? null,
        ]);
    }

    /**
     * Nouvelle note.
     *
     * @Route("support/{id}/note/new", name="note_new", methods="POST")
     */
    public function newNote(SupportGroup $supportGroup, Note $note = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $note = new Note();

        $form = ($this->createForm(NoteType::class, $note))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createNote($supportGroup, $note);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Modification d'une note.
     *
     * @Route("note/{id}/edit", name="note_edit", methods="POST")
     */
    public function editNote(Note $note, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $note);

        $form = ($this->createForm(NoteType::class, $note))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateNote($note, 'update');
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Supprime la note.
     *
     * @Route("note/{id}/delete", name="note_delete", methods="GET")
     * @IsGranted("DELETE", subject="note")
     */
    public function deleteNote(Note $note): Response
    {
        $this->manager->remove($note);
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'La note sociale est supprimée.',
        ], 200);
    }

    /**
     * Générer une note à partir de la dernière évaluation sociale du suivi.
     *
     * @Route("support/{id}/note/new_evaluation", name="support_note_new_evaluation", methods="GET")
     */
    public function generateNoteEvaluation(int $id, SupportGroupRepository $repoSupportGroup, EvaluationGroupRepository $repo, RdvRepository $repoRdv, Environment $renderer): Response
    {
        $supportGroup = $repoSupportGroup->findFullSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $evaluation = $repo->findEvaluationById($supportGroup);

        $note = (new Note())
            ->setTitle('Rapport social '.$evaluation->getUpdatedAt()->format('d/m/Y'))
            ->setContent($renderer->render('app/evaluation/evaluationExport.html.twig', [
                'support' => $supportGroup,
                'evaluation' => $evaluation,
                'nextRdv' => $repoRdv->findNextRdvFromSupport($supportGroup->getId()),
                'lastRdv' => $repoRdv->findLastRdvFromSupport($supportGroup->getId()),
            ]))
            ->setType(2)
            ->setSupportGroup($supportGroup)
            ->setCreatedBy($this->getUser());

        $this->manager->persist($note);
        $this->manager->flush();

        return $this->redirectToRoute('support_notes', [
            'id' => $supportGroup->getId(),
            'noteId' => $note->getId(),
        ]);
    }

    /**
     * @Route("note/{id}/export", name="note_export", methods="GET")
     */
    public function exportNote(Note $note, ExportWord $exportWord, ExportPDF $exportPDF): Response
    {
        // return $exportPDF->init();

        return $exportWord->export($note->getContent(), $note->getTitle(), $note->getSupportGroup()->getService()->getPole()->getLogoPath());
    }

    /**
     * Crée la note une fois le formulaire soumis et validé.
     */
    protected function createNote(SupportGroup $supportGroup, Note $note): Response
    {
        $note->setSupportGroup($supportGroup);

        $supportGroup->setUpdatedAt(new \DateTime());

        $this->manager->persist($note);
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'La note sociale est enregistrée.',
            'data' => [
                'noteId' => $note->getId(),
                'type' => $note->getTypeToString(),
                'status' => $note->getStatusToString(),
                'editInfo' => '| Créé le '.$note->getCreatedAt()->format('d/m/Y à H:i').' par '.$note->getCreatedBy()->getFullname(),
            ],
        ], 200);
    }

    /**
     * Met à jour la note une fois le formulaire soumis et validé.
     */
    protected function updateNote(Note $note, $typeSave): Response
    {
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => $typeSave,
            'alert' => 'success',
            'msg' => 'La note sociale est modifiée.',
            'data' => [
                'noteId' => $note->getId(),
                'type' => $note->getTypeToString(),
                'status' => $note->getStatusToString(),
                'editInfo' => '(modifié le '.$note->getUpdatedAt()->format('d/m/Y à H:i').' par '.$note->getUpdatedBy()->getFullname().')',
            ],
        ], 200);
    }
}
