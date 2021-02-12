<?php

namespace App\Controller\Support;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\Service;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\AvdlSupportSearch;
use App\Form\Support\Avdl\AvdlSupportSearchType;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\AvdlSupportPersonExport;
use App\Service\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AvdlController extends AbstractController
{
    use ErrorMessageTrait;

    private $serviceId;

    public function __construct()
    {
        $this->serviceId = Service::SERVICE_AVDL_ID;
    }

    /**
     * Liste des suivis AVDL.
     *
     * @Route("/avdl-supports", name="avdl_supports", methods="GET|POST")
     */
    public function viewListAvdlSupports(Request $request, Pagination $pagination, SupportPersonRepository $repo): Response
    {
        $form = ($this->createForm(AvdlSupportSearchType::class, $search = new AvdlSupportSearch()))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $repo);
        }

        return $this->render('app/support/avdl/listAvdlSupports.html.twig', [
            'form' => $form->createView(),
            'supports' => $pagination->paginate($repo->findAvdlSupportsQuery($search, $this->serviceId), $request),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(AvdlSupportSearch $search, SupportPersonRepository $repo)
    {
        set_time_limit(10 * 60);

        $supports = $repo->findSupportsOfServiceToExport($search, $this->serviceId);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('avdl_supports');
        }

        return (new AvdlSupportPersonExport())->exportData($supports);
    }
}
