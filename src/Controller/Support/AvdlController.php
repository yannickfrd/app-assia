<?php

declare(strict_types=1);

namespace App\Controller\Support;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Organization\Service;
use App\Form\Model\Support\AvdlSupportSearch;
use App\Form\Support\Avdl\AvdlSupportSearchType;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\AvdlSupportPersonExport;
use App\Service\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class AvdlController extends AbstractController
{
    use ErrorMessageTrait;

    /**
     * Liste des suivis AVDL.
     *
     * @Route("/avdl-supports", name="avdl_supports", methods="GET|POST")
     */
    public function viewListAvdlSupports(Request $request, Pagination $pagination, SupportPersonRepository $supportPersonRepo): Response
    {
        $form = $this->createForm(AvdlSupportSearchType::class, $search = new AvdlSupportSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $supportPersonRepo);
        }

        return $this->render('app/support/avdl/avdl_support_index.html.twig', [
            'form' => $form->createView(),
            'supports' => $pagination->paginate($supportPersonRepo->findAvdlSupportsQuery($search), $request),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(AvdlSupportSearch $search, SupportPersonRepository $supportPersonRepo)
    {
        $supports = $supportPersonRepo->findSupportsOfServiceToExport($search, Service::SERVICE_TYPE_AVDL);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('avdl_supports');
        }

        return (new AvdlSupportPersonExport())->exportData($supports);
    }
}
