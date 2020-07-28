<?php

namespace App\Controller;

use App\Entity\Export;
use App\Service\Download;
use App\Form\Model\Import;
use App\Service\Pagination;
use App\Service\ImportDatas;
use App\Form\Import\ImportType;
use App\Form\Model\ExportSearch;
use App\Repository\ExportRepository;
use App\Form\Export\ExportSearchType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportPersonRepository;
use App\Controller\Traits\ErrorMessageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExportController extends AbstractController
{
    use ErrorMessageTrait;

    protected $manager;
    protected $repo;

    public function __construct(EntityManagerInterface $manager, ExportRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Export des données.
     *
     * @Route("export", name="export", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function export(Request $request, ExportSearch $search = null, Pagination $pagination): Response
    {
        $search = new ExportSearch();

        $form = ($this->createForm(ExportSearchType::class, $search))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // $supports = $this->repoSupportPerson->findSupportsFullToExport($search);
            // return $exportSupport->exportData($supports);
            // $this->addFlash('success', 'Votre export est en cours de préparation... Vous recevrez le lien de téléchargement par email.');

            return $this->json([
                'code' => 200,
                'alert' => 'success',
                'type' => 'export',
                'msg' => 'Votre fichier d\'export est prêt. Un mail vous a été envoyé.',
            ], 200);
        }

        return $this->render('app/export/export.html.twig', [
            'form' => $form->createView(),
            'exports' => $pagination->paginate($this->repo->findExportsQuery(), $request, 10) ?? null,
        ]);
    }

    /**
     * Import de données.
     *
     * @Route("import", name="import", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function import(Request $request, Import $import, ImportDatas $importDatas): Response
    {
        $fileName = 'uploads/import.csv';

        $datas = $importDatas->getDatas($fileName);

        $form = ($this->createForm(ImportType::class, $import))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $importDatas->importInDatabase($fileName, $import->getService());
        } else {
            dump($datas);
        }

        return $this->render('app/import.html.twig', [
            'form' => $form->createView(),
            'datas' => $datas,
        ]);
    }

    /**
     * Compte le nombre de résultats.
     *
     * @Route("export/count_results", name="export_count_results", methods="POST")
     */
    public function countNbResults(Request $request, ExportSearch $search = null, SupportPersonRepository $repo): Response
    {
        $search = new ExportSearch();

        $form = ($this->createForm(ExportSearchType::class, $search))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $count = $repo->countSupportsToExport($search);

            return $this->json([
                    'code' => 200,
                    'alert' => 'success',
                    'type' => 'count',
                    'count' => $count,
                    'msg' => 'Nombre de résultats : '.$count,
                ], 200);
        }

        $this->getErrorMessage($form);
    }

    /**
     * Donne le fichier d'export.
     *
     * @Route("export/{id}/get", name="export_get", methods="GET")
     */
    public function getExport(Export $export, Download $download): Response
    {
        $this->denyAccessUnlessGranted('GET', $export);

        if (file_exists($export->getFileName())) {
            return $download->send($export->getFileName());
        }

        $this->addFlash('danger', 'Ce fichier n\'existe pas.');

        return $this->redirectToRoute('export');
    }

    /**
     * Supprime le fichier d'export.
     *
     * @Route("export/{id}/delete", name="export_delete", methods="GET")
     */
    public function deleteExport(Export $export): Response
    {
        if (file_exists($export->getFileName())) {
            unlink($export->getFileName());
        }

        $this->manager->remove($export);
        $this->manager->flush();

        $this->addFlash('warning', 'Le fichier d\'export est supprimé.');

        return $this->redirectToRoute('export');
    }
}
