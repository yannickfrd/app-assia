<?php

namespace App\Controller;

use App\Form\Model\Export;
use App\Form\Support\ExportType;

use App\Export\SupportPersonExport;

use Doctrine\ORM\EntityManagerInterface;

use App\Repository\SupportPersonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExportController extends AbstractController
{
    private $manager;
    private $repo;
    private $security;
    private $currentUser;

    public function __construct(EntityManagerInterface $manager, SupportPersonRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
        $this->currentUser = $security->getUser();
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
