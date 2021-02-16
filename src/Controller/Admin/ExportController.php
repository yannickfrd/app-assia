<?php

namespace App\Controller\Admin;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Admin\Export;
use App\Event\SupportPersonExportEvent;
use App\Form\Admin\ExportSearchType;
use App\Form\Model\Admin\ExportSearch;
use App\Repository\Admin\ExportRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Download;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController
{
    use ErrorMessageTrait;

    private $repo;

    public function __construct(ExportRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Export des données.
     *
     * @Route("export", name="export", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     */
    public function export(Request $request, Pagination $pagination, EventDispatcherInterface $dispatcher): Response
    {
        $form = ($this->createForm(ExportSearchType::class, $search = new ExportSearch()))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new SupportPersonExportEvent($search), 'support_person.full_export');

            return $this->json([
                'code' => 200,
                'alert' => 'success',
                'type' => 'export',
                'msg' => 'Votre fichier d\'export est prêt. Un mail vous a été envoyé.',
            ]);
        }

        return $this->render('app/admin/export/export.html.twig', [
            'form' => $form->createView(),
            'exports' => $pagination->paginate($this->repo->findExportsQuery(), $request, 10),
        ]);
    }

    /**
     * Compte le nombre de résultats.
     *
     * @Route("export/count_results", name="export_count_results", methods="POST")
     */
    public function countNbResults(Request $request, SupportPersonRepository $repo): Response
    {
        $form = ($this->createForm(ExportSearchType::class, $search = new ExportSearch()))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->json([
                    'code' => 200,
                    'alert' => 'success',
                    'type' => 'count',
                    'count' => $count = $repo->countSupportsToExport($search),
                    'msg' => 'Nombre de résultats : '.number_format($count, 0, '', ' '),
                ]);
        }

        return $this->getErrorMessage($form);
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
    public function deleteExport(Export $export, EntityManagerInterface $manager): Response
    {
        if (file_exists($export->getFileName())) {
            unlink($export->getFileName());
        }

        $manager->remove($export);
        $manager->flush();

        $this->addFlash('warning', 'Le fichier d\'export est supprimé.');

        return $this->redirectToRoute('export');
    }
}
