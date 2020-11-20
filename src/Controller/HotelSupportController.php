<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Form\HotelSupport\HotelSupportSearchType;
use App\Form\Model\HotelSupportSearch;
use App\Repository\SupportPersonRepository;
use App\Service\Export\HotelSupportPersonExport;
use App\Service\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HotelSupportController extends AbstractController
{
    private $serviceId;

    public function __construct()
    {
        $this->serviceId = Service::SERVICE_PASH_ID;
    }

    /**
     * Liste des suivis AVDL.
     *
     * @Route("/hotel-supports", name="hotel_supports", methods="GET|POST")
     */
    public function viewListHotelSupports(Request $request, Pagination $pagination, SupportPersonRepository $repo): Response
    {
        $search = (new HotelSupportSearch())->setStatus([SupportGroup::STATUS_IN_PROGRESS]);

        $form = ($this->createForm(HotelSupportSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $repo);
        }

        return $this->render('app/hotelSupport/listHotelSupports.html.twig', [
            'SupportSearch' => $search,
            'form' => $form->createView(),
            'supports' => $pagination->paginate(
                $repo->findHotelSupportsQuery($search, $this->serviceId),
                $request
            ),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(HotelSupportSearch $search, SupportPersonRepository $repo)
    {
        $supports = $repo->findSupportsFromServiceToExport($search, $this->serviceId);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('hotel_supports');
        }

        return (new HotelSupportPersonExport())->exportData($supports);
    }
}
