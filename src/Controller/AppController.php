<?php

namespace App\Controller;

use App\Form\Model\Export;
use App\Form\Support\ExportType;
use App\Repository\RdvRepository;
use App\Repository\NoteRepository;
use App\Export\SupportPersonExport;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
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

    /**
     * Export des données
     * 
     * @param Export $export
     * @param Request $request
     * @param SupportPersonRepository $repoSupportPerson
     * @return Response
     */
    public function export(Export $export = null, Request $request, SupportPersonRepository $repoSupportPerson): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $export = new Export();

        $form = $this->createForm(ExportType::class, $export);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $supports = $repoSupportPerson->findSupportsToExport($export);
            $exportSupport = new SupportPersonExport();
            return $exportSupport->exportData($supports);
        }

        return $this->render("app/export.html.twig", [
            "form" => $form->createView()
        ]);
    }
}
