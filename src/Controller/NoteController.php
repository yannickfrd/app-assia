<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\SupportGroup;
use App\Form\Model\NoteSearch;
use App\Form\Note\NoteType;
use App\Form\Note\NoteSearchType;
use App\Repository\NoteRepository;
use App\Repository\SupportGroupRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NoteController extends AbstractController
{
    private $manager;
    private $repo;
    private $repoSupportGroup;

    public function __construct(EntityManagerInterface $manager, NoteRepository $repo, SupportGroupRepository $repoSupportGroup)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->repoSupportGroup = $repoSupportGroup;
    }

    /**
     * Liste des notes
     * 
     * @Route("support/{id}/notes", name="notes")
     * @param SupportGroup $supportGroup
     * @param NoteSearch $noteSearch
     * @param Note $note
     * @param Request $request
     * @param Pagination $pagination
     * @return Response
     */
    public function listNotes($id, NoteSearch $noteSearch = null, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);

        $this->denyAccessUnlessGranted("VIEW", $supportGroup);

        $noteSearch = new NoteSearch;

        $formSearch = $this->createForm(NoteSearchType::class, $noteSearch);
        $formSearch->handleRequest($request);

        $notes =  $pagination->paginate($this->repo->findAllNotesQuery($supportGroup->getId(), $noteSearch), $request);

        $form = $this->createForm(NoteType::class, new Note());

        return $this->render("app/note/listNotes.html.twig", [
            "support" => $supportGroup,
            "form_search" => $formSearch->createView(),
            "form" => $form->createView(),
            "notes" => $notes ?? null,
        ]);
    }

    /**
     * Nouvelle note
     * 
     * @Route("support/{id}/note/new", name="note_new", methods="POST") *
     * @param int $id
     * @param Note $note
     * @param Request $request
     * @return Response
     */
    public function newNote($id, Note $note = null, Request $request): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($id);

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $note = new Note();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createNote($supportGroup, $note);
        }
        return $this->errorMessage();
    }

    /**
     * Modification d'une note
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
        return $this->errorMessage();
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
        $this->denyAccessUnlessGranted("DELETE", $note->getSupportGroup());

        $this->manager->remove($note);
        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => "delete",
            "alert" => "warning",
            "msg" => "La note sociale a été supprimée.",
        ], 200);
    }

    /**
     * Crée la note une fois le formulaire soumis et validé
     *
     * @param SupportGroup $supportGroup
     * @param Note $note
     * @return Response
     */
    protected function createNote(SupportGroup $supportGroup, Note $note): Response
    {
        $now = new \DateTime();

        $note->setSupportGroup($supportGroup)
            ->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

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
                "editInfo" => "| Créé le " . date_format($now, "d/m/Y à H:i") .  " par " . $note->getCreatedBy()->getFullname()
            ]
        ], 200);
    }

    /**
     * Met à jour la note une fois le formulaire soumis et validé
     *
     * @param Note $note
     * @return Response
     */
    protected function updateNote(Note $note, $typeSave): Response
    {
        $note->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

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

    /**
     * Retourne un message d'erreur au format JSON
     * 
     * @return Response
     */
    protected function errorMessage(): Response
    {
        return $this->json([
            "code" => 200,
            "alert" => "danger",
            "msg" => "Une erreur s'est produite. Le document n'a pas pu être enregistré.",
        ], 200);
    }
}
