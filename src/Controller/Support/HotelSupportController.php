<?php

namespace App\Controller\Support;

use App\Entity\Organization\Service;
use App\Form\Model\Support\HotelSupportSearch;
use App\Form\Support\HotelSupport\HotelSupportSearchType;
use App\Repository\Support\SupportPersonRepository;
use App\Service\Export\HotelSupportPersonExport;
use App\Service\Pagination;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HotelSupportController extends AbstractController
{
    /**
     * Liste des suivis AVDL.
     *
     * @Route("/hotel-supports", name="hotel_supports", methods="GET|POST")
     */
    public function viewListHotelSupports(Request $request, Pagination $pagination, SupportPersonRepository $repo): Response
    {
        $form = ($this->createForm(HotelSupportSearchType::class, $search = new HotelSupportSearch()))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $repo);
        }

        return $this->render('app/support/hotelSupport/listHotelSupports.html.twig', [
            'form' => $form->createView(),
            'supports' => $pagination->paginate(
                $repo->findHotelSupportsQuery($search),
                $request
            ),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(HotelSupportSearch $search, SupportPersonRepository $repo)
    {
        set_time_limit(10 * 60);

        $supports = $repo->findSupportsOfServiceToExport($search, Service::SERVICE_TYPE_HOTEL);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('hotel_supports');
        }

        return (new HotelSupportPersonExport())->exportData($supports);
    }
}
