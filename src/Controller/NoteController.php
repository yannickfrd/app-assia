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
use App\Repository\NoteRepository;
use App\Repository\RdvRepository;
use App\Repository\SupportGroupRepository;
use App\Security\CurrentUserService;
use App\Service\ExportWord;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class NoteController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repoNote;

    public function __construct(EntityManagerInterface $manager, NoteRepository $repoNote)
    {
        $this->manager = $manager;
        $this->repoNote = $repoNote;
    }

    /**
     * Liste des notes.
     *
     * @Route("notes", name="notes", methods="GET|POST")
     */
    public function listNotes(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $search = new NoteSearch();
        if (User::STATUS_SOCIAL_WORKER == $this->getUser()->getStatus()) {
            $usersCollection = new ArrayCollection();
            $usersCollection->add($this->getUser());
            $search->setReferents($usersCollection);
        }

        $form = ($this->createForm(NoteSearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/note/listNotes.html.twig', [
            'form' => $form->createView(),
            'notes' => $pagination->paginate($this->repoNote->findAllNotesQuery($search, $currentUser),
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
    public function listSupportNotes(int $id, SupportManager $supportManager, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $search = new SupportNoteSearch();

        $formSearch = $this->createForm(SupportNoteSearchType::class, $search)
            ->handleRequest($request);

        $form = $this->createForm(NoteType::class, new Note());

        return $this->render('app/note/supportNotes.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'nbTotalNotes' => $supportManager->getNbNotes($supportGroup, $this->repoNote),
            'notes' => $this->getNotes($supportGroup, $request, $search, $pagination),
        ]);
    }

    /**
     * Donne les notes du suivi.
     */
    protected function getNotes(SupportGroup $supportGroup, Request $request, SupportNoteSearch $search, Pagination $pagination)
    {
        // Si filtre ou tri utilisé, n'utilise pas le cache.
        if ($request->query->count() > 0) {
            return $pagination->paginate($this->repoNote->findAllNotesFromSupportQuery($supportGroup->getId(), $search), $request, 10);
        }

        // Sinon, récupère les notes en cache.
        return (new FilesystemAdapter())->get(SupportGroup::CACHE_SUPPORT_NOTES_KEY.$supportGroup->getId(),
            function (CacheItemInterface $item) use ($supportGroup, $pagination, $search, $request) {
                $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

                return $pagination->paginate($this->repoNote->findAllNotesFromSupportQuery($supportGroup->getId(), $search), $request, 10);
            }
        );
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

        $this->discache($note->getSupportGroup());

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
    public function generateNoteEvaluation(int $id, SupportGroupRepository $repoSupport, SupportManager $supportManager, RdvRepository $repoRdv, Environment $renderer): Response
    {
        $supportGroup = $repoSupport->findSupportById($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $evaluation = $supportManager->getEvaluation($supportGroup);

        $note = (new Note())
            ->setTitle('Grille d\'évaluation sociale '.$evaluation->getUpdatedAt()->format('d/m/Y'))
            ->setContent($renderer->render('app/evaluation/evaluationExport.html.twig', [
                'support' => $supportGroup,
                'evaluation' => $evaluation,
                'lastRdv' => $supportManager->getLastRdvs($supportGroup, $repoRdv),
                'nextRdv' => $supportManager->getNextRdvs($supportGroup, $repoRdv),
                ]))
            ->setType(2)
            ->setSupportGroup($supportGroup)
            ->setCreatedBy($this->getUser());

        $this->manager->persist($note);
        $this->manager->flush();

        $this->discache($supportGroup);

        return $this->redirectToRoute('support_notes', [
            'id' => $id,
            'noteId' => $note->getId(),
        ]);
    }

    /**
     * @Route("note/{id}/export", name="note_export", methods="GET")
     */
    public function exportNote(int $id, Request $request, ExportWord $exportWord): Response
    {
        $note = $this->repoNote->findNote($id);
        $supportGroup = $note->getSupportGroup();
        $fullname = $this->getHeadSupportPerson($supportGroup);

        $exportWord->createDocument($note->getContent(), $note->getTitle(), $supportGroup->getService()->getPole()->getLogoPath(), $fullname);

        return $exportWord->save($request->server->get('HTTP_USER_AGENT') != 'Symfony BrowserKit');
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

        $this->discache($supportGroup);

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

        $this->discache($note->getSupportGroup(), true);

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

    /**
     * Supprime les notes en cache du suivi et de l'utlisateur.
     */
    protected function discache(SupportGroup $supportGroup, $isUpdate = false): bool
    {
        $cache = new FilesystemAdapter();

        if (false === $isUpdate) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_NOTES_KEY.$supportGroup->getId());
        }

        return $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NOTES_KEY.$supportGroup->getId());
    }

    /**
     * Donne le demandeur principal du suivi.
     */
    protected function getHeadSupportPerson(SupportGroup $supportGroup): ?string
    {
        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            if (true === $supportPerson->getHead()) {
                return $supportPerson->getPerson()->getFullname();
            }
        }

        return null;
    }
}
