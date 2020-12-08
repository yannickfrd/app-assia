<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Form\Avdl\AvdlSupportSearchType;
use App\Form\Model\AvdlSupportSearch;
use App\Repository\SupportPersonRepository;
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
        $search = (new AvdlSupportSearch())->setStatus([SupportGroup::STATUS_IN_PROGRESS]);

        $form = ($this->createForm(AvdlSupportSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $repo);
        }

        return $this->render('app/avdl/listAvdlSupports.html.twig', [
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

        $supports = $repo->findSupportsFromServiceToExport($search, $this->serviceId);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('avdl_supports');
        }

        return (new AvdlSupportPersonExport())->exportData($supports);
    }
}
