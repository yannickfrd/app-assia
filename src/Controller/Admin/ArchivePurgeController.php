<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Model\Support\SupportSearch;
use App\Form\Support\Support\SupportSearchType;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Admin\Archiver;
use App\Service\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin", name="admin_")
 * @IsGranted("ROLE_SUPER_ADMIN")
 */
final class ArchivePurgeController extends AbstractController
{
    private Archiver $archiver;

    public function __construct(Archiver $archiver)
    {
        $this->archiver = $archiver;
    }

    /**
     * @Route("/archive-purge", name="archive_purge_index", methods="GET")
     */
    public function index(): Response
    {
        return $this->render('app/admin/archive_purge.html.twig', $this->archiver->getStats());
    }

    /**
     * @Route("/archives", name="archive_index", methods="GET")
     * @Route("/purges", name="purge_index", methods="GET")
     */
    public function archivePurgeIndex(
        Request $request,
        SupportPersonRepository $supportPersonRepo,
        Pagination $pagination
    ): Response {
        $form = $this->createForm(SupportSearchType::class, $search = (new SupportSearch())->setStatus([]))
            ->handleRequest($request);

        $isArchive = 'admin_archive_index' === $request->get('_route');
        $supportGroupIds = $this->archiver->getSupportGroupIds($isArchive ? 'archive' : 'purge');

        return $this->renderForm('app/admin/archive_purge_index.html.twig', [
            'is_archive' => $isArchive,
            'form' => $form,
            'supports' => $pagination->paginate(
                $supportPersonRepo->findSupportPeopleBySupportGroupIdsQuery($supportGroupIds, $search),
                $request,
                100
            ),
        ]);
    }

    /**
     * @Route("/archive", name="archive", methods="GET")
     */
    public function archive(): RedirectResponse
    {
        $this->archiver->archive();

        $this->addFlash('success', 'admin.archive_is_successful');

        return $this->redirectToRoute('admin_archive_purge_index');
    }

    /**
     * @Route("/purge", name="purge", methods="GET")
     */
    public function purge(): RedirectResponse
    {
        $this->archiver->purge();

        $this->addFlash('success', 'admin.purge_is_successful');

        return $this->redirectToRoute('admin_archive_purge_index');
    }
}
