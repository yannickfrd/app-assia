<?php

namespace App\Controller;

use App\Repository\RdvRepository;
use App\Repository\NoteRepository;
use App\Repository\SupportGroupRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     * @Route("/")
     * @return Response
     */
    public function home(SupportGroupRepository $repoSupport, NoteRepository $repoNote, RdvRepository $repoRdv): Response
    {
        $supports = $repoSupport->findAllSupportsFromUser($this->getUser());
        $notes = $repoNote->findAllNotesFromUser($this->getUser(), 10);
        $rdvs = $repoRdv->findAllRdvsFromUser($this->getUser(), 10);

        return $this->render("app/home.html.twig", [
            "supports" => $supports,
            "notes" => $notes,
            "rdvs" => $rdvs
        ]);
    }
}
