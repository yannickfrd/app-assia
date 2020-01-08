<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\NoteSearch;
use App\Entity\SupportGroup;

use App\Form\Support\Note\NoteType;
use App\Form\Support\Note\NoteSearchType;

use App\Repository\NoteRepository;
use App\Repository\SupportGroupRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NoteController extends AbstractController
{
    private $manager;
    private $repo;
    private $currentUser;

    public function __construct(EntityManagerInterface $manager, NoteRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
        $this->currentUser = $security->getUser();
    }

    /**
     * Liste des notes
     * 
     * @Route("support/{id}/note/list", name="note_list")
     *
     * @param SupportGroup $supportGroup
     * @param NoteSearch $noteSearch
     * @param Note $note
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function listNotes($id, SupportGroupRepository $supportRepo, NoteSearch $noteSearch = null, Note $note = null, Request $request, PaginatorInterface $paginator): Response
    {
        $supportGroup = $supportRepo->findSupportById($id);

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $noteSearch = new NoteSearch;

        $formSearch = $this->createForm(NoteSearchType::class, $noteSearch);
        $formSearch->handleRequest($request);

        $notes =  $this->paginate($paginator, $supportGroup, $noteSearch, $request);

        if ($note == null) {
            $note = new Note();
        }

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->createNote($supportGroup, $note);
        }

        return $this->render("note/listNotes.html.twig", [
            "support" => $supportGroup,
            "form_search" => $formSearch->createView(),
            "form" => $form->createView(),
            "notes" => $notes ?? null,
        ]);
    }

    /**
     * Modifie la note
     * 
     * @Route("note/{id}/edit", name="note_edit")
     * @param Note $note
     * @param Request $request
     * @return Response
     */
    public function editNote(Note $note, Request $request): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $note->getSupportGroup());

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->updateNote($note, "update");
        }

        return $this->render("note/note.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true,
        ]);
    }

    /**
     * Supprime la note
     * 
     * @Route("note/{id}/delete", name="note_delete")
     * @param Note $note
     * @return Response
     */
    public function deleteNote(Note $note): Response
    {
        $this->denyAccessUnlessGranted("EDIT", $note->getSupportGroup());

        $this->manager->remove($note);
        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => "delete",
            "alert" => "warning",
            "msg" => "La note sociale a été supprimée.",
        ], 200);
    }

    // Pagination de la liste des notes
    protected function paginate($paginator, $supportGroup, $noteSearch, $request)
    {
        $notes =  $paginator->paginate(
            $this->repo->findAllNotesQuery($supportGroup->getId(), $noteSearch),
            $request->query->getInt("page", 1), // page number
            6 // limit per page
        );
        $notes->setCustomParameters([
            "align" => "right", // align pagination
        ]);

        return $notes;
    }

    // Crée la note une fois le formulaire soumis et validé
    protected function createNote($supportGroup, $note)
    {
        $note->setSupportGroup($supportGroup)
            ->setCreatedAt(new \DateTime())
            ->setCreatedBy($this->currentUser)
            ->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->currentUser);

        $this->manager->persist($note);
        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => "create",
            "alert" => "success",
            "msg" => "La note sociale a été enregistrée.",
            "data" => [
                "noteId" => $note->getId(),
                "type" => $note->getTypeList(),
                "status" => $note->getStatusList(),
                "editInfo" => "| Créé le " . date_format($note->getCreatedAt(), "d/m/Y à H:i") .  " par " . $note->getCreatedBy()->getFullname()
            ]
        ], 200);
    }

    // Met à jour la note une fois le formulaire soumis et validé
    protected function updateNote($note, $typeSave)
    {
        $note->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->currentUser);

        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => $typeSave,
            "alert" => "success",
            "msg" => "La note sociale a été modifiée.",
            "data" => [
                "noteId" => $note->getId(),
                "type" => $note->getTypeList(),
                "status" => $note->getStatusList(),
                "editInfo" => "(modifié le " . date_format($note->getUpdatedAt(), "d/m/Y à H:i") . " par " . $note->getUpdatedBy()->getFullname() . ")"
            ]
        ], 200);
    }
}
