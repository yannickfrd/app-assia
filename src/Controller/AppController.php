<?php

namespace App\Controller;

use App\Entity\EvaluationPerson;
use App\Form\Model\Export;
use App\Form\Support\ExportType;
use App\Repository\RdvRepository;
use App\Repository\NoteRepository;
use App\Export\SupportPersonExport;
use App\Repository\EvaluationPersonRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
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
    public function home(SupportGroupRepository $repoSupport, NoteRepository $repoNote, RdvRepository $repoRdv, UserRepository $repo): Response
    {
        $user = $repo->findOneBy(["username" => "r.madelaine"]);
        $supports = $repoSupport->findAllSupportsFromUser($user);
        $notes = $repoNote->findAllNotesFromUser($user, 10);
        $rdvs = $repoRdv->findAllRdvsFromUser($user, 10);

        return $this->render("app/home.html.twig", [
            "user" => $user,
            "supports" => $supports,
            "notes" => $notes,
            "rdvs" => $rdvs
        ]);
    }
}
