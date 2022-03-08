<?php

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

/**
 * @IsGranted("ROLE_ADMIN")
 */
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
     * @Route("/exports", name="export_index", methods="GET|POST")
     */
    public function index(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(ExportSearchType::class, new ExportSearch());

        return $this->renderForm('app/admin/export/export_index.html.twig', [
            'form' => $form,
            'exports' => $pagination->paginate($this->exportRepo->findExportsQuery(), $request, 10),
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
                    'msg' => 'Le nombre de résultats ('.number_format($nbResults, 0, '', ' ').') est supérieur  à la 
                        limite autorisée ('.number_format(SupportPersonRepository::EXPORT_LIMIT, 0, '', ' ').').',
                ]);
            }

            $export = $exportManager->create($nbResults, $search);

            if (null !== $export) {
                return $this->json([
                    'action' => 'create',
                    'alert' => 'success',
                    'msg' => 'Votre export est en cours de préparation... Vous recevrez le lien de téléchargement par email.',
                    'export' => $normalizer->normalize($export, 'json', ['groups' => 'show_export']),
                    'path' => $this->generateUrl('export_send', ['id' => $export->getId()]),
                ]);
            }
        }

        return $this->json([
            'alert' => 'danger',
            'msg' => 'Une erreur s\'est produite.',
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
                'msg' => 'Le fichier est prêt. Vous avez reçu le lien de téléchargement par email.',
                'export' => $normalizer->normalize($export, 'json', ['groups' => 'show_export']),
                'path' => $this->generateUrl('export_download', ['id' => $export->getId()]),
            ]);
        }

        return $this->json([
            'alert' => 'danger',
            'msg' => 'Une erreur s\'est produite.',
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
                'msg' => 'Nombre de résultats : '.number_format($nbResults, 0, '', ' '),
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
            return $downloader->send($export->getFileName());
        }

        return $this->redirectToRoute('export');
    }

    /**
     * @Route("/export/{id}/delete", name="export_delete", methods="GET")
     */
    public function delete(?Export $export, EntityManagerInterface $em): JsonResponse
    {
        try {
            $this->denyAccessUnlessGranted('DELETE', $export);

            if (file_exists($export->getFileName())) {
                unlink($export->getFileName());
            }

            $exportId = $export->getId();

            $em->remove($export);
            $em->flush();

            return $this->json([
                    'action' => 'delete',
                    'export' => ['id' => $exportId],
                    'alert' => 'warning',
                    'msg' => 'Le fichier d\'export est supprimé.',
            ]);

            $this->addFlash('warning', '');
        } catch (\Exception $e) {
            return $this->json([
                'alert' => 'danger',
                'msg' => 'Une erreur s\'est produite.',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
