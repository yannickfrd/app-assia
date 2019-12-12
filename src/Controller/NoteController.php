<?php

namespace App\Controller;

use App\Entity\Note;
use App\Entity\NoteSearch;
use App\Entity\SupportGroup;

use App\Form\Support\Note\NoteType;
use App\Form\Support\Note\NoteSearchType;

use App\Repository\NoteRepository;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NoteController extends AbstractController
{
    private $manager;
    private $repo;
    private $security;

    public function __construct(ObjectManager $manager, NoteRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }


    /**
     * Liste des notes
     * 
     * @Route("support/{id}/note/list", name="note_list")
     * @return Response
     */
    public function listPeople(SupportGroup $supportGroup, NoteSearch $noteSearch = null, Request $request, PaginatorInterface $paginator): Response
    {
        $noteSearch = new NoteSearch;

        $form = $this->createForm(NoteSearchType::class, $noteSearch);
        $form->handleRequest($request);

        $notes =  $paginator->paginate(
            $this->repo->findAllNotesQuery($supportGroup->getId(), $noteSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $notes->setCustomParameters([
            "align" => "right", // align pagination
        ]);

        return $this->render("note/listNotes.html.twig", [
            "support" => $supportGroup,
            "form" => $form->createView(),
            "notes" => $notes ?? null,
        ]);
    }

    /**
     * @Route("support/{id}/note/create", name="note_create")
     *
     * @param Note $note
     * @param Request $request
     * @return Response
     */
    public function createNote(SupportGroup $supportGroup, Note $note = null, Request $request): Response
    {
        $note = new Note();

        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->security->getUser();

            $note->setSupportGroup($supportGroup)
                ->setUser($user)
                ->setCreatedAt(new \DateTime())
                ->setCreatedBy($user)
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user);

            $this->manager->persist($note);

            $this->manager->flush();

            $this->addFlash("success", "La note sociale a été enregistrée.");

            return $this->redirectToRoute("note_list", [
                "id" => $supportGroup->getId(),
                "notes" => $notes ?? null,
            ]);
        }

        return $this->render("note/note.html.twig", [
            "support" => $supportGroup,
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * @Route("note/{id}/edit", name="note_edit")
     *
     * @param Note $note
     * @param Request $request
     * @return Response
     */
    public function editNote(Note $note, Request $request): Response
    {
        $form = $this->createForm(NoteType::class, $note);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->security->getUser();

            $note->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user);

            $this->manager->flush();

            $this->addFlash("success", "La note sociale a été modifiée.");

            return $this->redirectToRoute("note_list", [
                "id" => $note->getSupportGroup()->getId()
            ]);
        }

        return $this->render("note/note.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }
}
