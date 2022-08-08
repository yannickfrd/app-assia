<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Admin\Export;
use App\Form\Admin\ExportSearchType;
use App\Form\Model\Admin\ExportSearch;
use App\Repository\Admin\ExportRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\ExportManager;
use App\Service\File\Downloader;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_ADMIN")
 */
final class ExportController extends AbstractController
{
    use ErrorMessageTrait;

    private $exportRepo;
    private $translator;

    public function __construct(ExportRepository $exportRepo, TranslatorInterface $translator)
    {
        $this->exportRepo = $exportRepo;
        $this->translator = $translator;
    }

    /**
     * Export des données.
     *
     * @Route("/exports", name="export_index", methods="GET|POST")
     */
    public function index(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(ExportSearchType::class, new ExportSearch());

        return $this->renderForm('app/admin/export/export_index.html.twig', [
            'form' => $form,
            'exports' => $pagination->paginate($this->exportRepo->findExportsQuery(), $request, 10),
            'export' => new Export(),
        ]);
    }

    /**
     * @Route("/export/new", name="export_new", methods="POST")
     */
    public function new(Request $request, SupportPersonRepository $supportPersonRepo,
        ExportManager $exportManager, NormalizerInterface $normalizer): JsonResponse
    {
        $form = $this->createForm(ExportSearchType::class, $search = new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $nbResults = $supportPersonRepo->countSupportsToExport($search);

            if ($nbResults > SupportPersonRepository::EXPORT_LIMIT) {
                return $this->json([
                    'action' => 'count',
                    'alert' => 'danger',
                    'count' => $nbResults,
                    'msg' => $this->translator->trans('export.limit', [
                        'count' => number_format($nbResults, 0, '', ' '),
                        'limit' => number_format(SupportPersonRepository::EXPORT_LIMIT, 0, '', ' '),
                    ], 'app'),
                ]);
            }

            $export = $exportManager->create($nbResults, $search);

            if (null !== $export) {
                return $this->json([
                    'action' => 'create',
                    'alert' => 'success',
                    'msg' => $this->translator->trans('export.in_progress', [], 'app'),
                    'export' => $normalizer->normalize($export, 'json', ['groups' => 'show_export']),
                    'path' => $this->generateUrl('export_send', ['id' => $export->getId()]),
                ]);
            }
        }

        return $this->json([
            'alert' => 'danger',
            'msg' => $this->translator->trans('error_occurred', [], 'app'),
        ]);
    }

    /**
     * Génère le fichier d'export.
     *
     * @Route("/export/{id}/send", name="export_send", methods="POST")
     */
    public function send(Export $export, Request $request, ExportManager $exportManager,
        NormalizerInterface $normalizer): JsonResponse
    {
        $form = $this->createForm(ExportSearchType::class, $search = new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $export = $exportManager->send($export, $search);

            return $this->json([
                'action' => 'export',
                'alert' => 'success',
                'msg' => $this->translator->trans('export.file_ready', [], 'app'),
                'export' => $normalizer->normalize($export, 'json', ['groups' => 'show_export']),
                'path' => $this->generateUrl('export_download', ['id' => $export->getId()]),
            ]);
        }

        return $this->json([
            'alert' => 'danger',
            'msg' => $this->translator->trans('error_occurred', [], 'app'),
        ]);
    }

    /**
     * Compte le nombre de résultats.
     *
     * @Route("/export/count", name="export_count", methods="POST")
     */
    public function count(Request $request, SupportPersonRepository $supportPersonRepo): JsonResponse
    {
        $form = $this->createForm(ExportSearchType::class, $search = new ExportSearch())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->json([
                'action' => 'count',
                'nbResults' => $nbResults = $supportPersonRepo->countSupportsToExport($search),
                'alert' => 'success',
                'msg' => $this->translator->trans('export.count_results', [
                    'count' => number_format($nbResults, 0, '', ' '),
                ], 'app'),
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Donne le fichier d'export.
     *
     * @Route("/export/{id}/download", name="export_download", methods="GET")
     */
    public function download(Export $export, Downloader $downloader): Response
    {
        $this->denyAccessUnlessGranted('GET', $export);

        if (file_exists($export->getFileName())) {
            return $downloader->send($export->getFileName(), ['action-type' => 'download']);
        }

        return $this->redirectToRoute('export_index');
    }

    /**
     * @Route("/export/{id}/delete", name="export_delete", methods="DELETE")
     */
    public function delete(?Export $export, EntityManagerInterface $em): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('DELETE', $export);

            if ($export->getFileName() && file_exists($export->getFileName())) {
                unlink($export->getFileName());
            }

            $exportId = $export->getId();

            $em->remove($export);
            $em->flush();

            return $this->json([
                    'action' => 'delete',
                    'export' => ['id' => $exportId],
                    'alert' => 'warning',
                    'msg' => $this->translator->trans('export.deleted_successfully', [], 'app'),
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'alert' => 'danger',
                'msg' => $this->translator->trans('error_occurred', [], 'app'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Donne le fichier modèle Excel de traitement statistique.
     *
     * @Route("/export/download-model", name="export_download_model", methods="GET")
     */
    public function downloadModel(Downloader $downloader): Response
    {
        try {
            return $downloader->send(\dirname(__DIR__).'/../../public/documentastion/models/modele-export.xlsx');
        } catch (\Exception $e) {
            $this->addFlash('danger', $this->translator->trans('error_occurred_with_msg', ['msg' => $e->getMessage()], 'app'));

            return $this->redirectToRoute('export_index');
        }
    }
}
