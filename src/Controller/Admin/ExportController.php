<?php

namespace App\Controller\Admin;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Admin\Export;
use App\Form\Admin\ExportSearchType;
use App\Form\Model\Admin\ExportSearch;
use App\Repository\Admin\ExportRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\File\Downloader;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
    use ErrorMessageTrait;

    private $exportRepo;

    public function __construct(ExportRepository $exportRepo)
    {
        $this->exportRepo = $exportRepo;
    }

    /**
     * Export des données.
     *
     * @Route("/export", name="export", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function export(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(ExportSearchType::class, new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->json([
                'alert' => 'success',
                'type' => 'export',
                'msg' => 'Votre fichier d\'export est prêt. Un mail vous a été envoyé.',
            ]);
        }

        return $this->render('app/admin/export/export.html.twig', [
            'form' => $form->createView(),
            'exports' => $pagination->paginate($this->exportRepo->findExportsQuery(), $request, 10),
        ]);
    }

    /**
     * Compte le nombre de résultats.
     *
     * @Route("/export/count_results", name="export_count_results", methods="POST")
     */
    public function countNbResults(Request $request, SupportPersonRepository $supportPersonRepo): Response
    {
        $form = $this->createForm(ExportSearchType::class, $search = new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->json([
                    'alert' => 'success',
                    'type' => 'count',
                    'count' => $count = $supportPersonRepo->countSupportsToExport($search),
                    'msg' => 'Nombre de résultats : '.number_format($count, 0, '', ' '),
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Donne le fichier d'export.
     *
     * @Route("/export/{id}/get", name="export_get", methods="GET")
     */
    public function getExport(Export $export, Downloader $downloader): Response
    {
        $this->denyAccessUnlessGranted('GET', $export);

        if (file_exists($export->getFileName())) {
            return $downloader->send($export->getFileName());
        }

        return $this->redirectToRoute('export');
    }

    /**
     * Supprime le fichier d'export.
     *
     * @Route("/export/{id}/delete", name="export_delete", methods="GET")
     */
    public function deleteExport(Export $export, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('DELETE', $export);

        if (file_exists($export->getFileName())) {
            unlink($export->getFileName());
        }

        $manager->remove($export);
        $manager->flush();

        $this->addFlash('warning', 'Le fichier d\'export est supprimé.');

        return $this->redirectToRoute('export');
    }
}
