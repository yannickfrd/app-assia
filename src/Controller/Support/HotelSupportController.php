<?php

namespace App\Controller\Support;

use App\Entity\Organization\Service;
use App\Entity\Support\SupportGroup;
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

        return $this->render('app/support/hotelSupport/listHotelSupports.html.twig', [
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
        set_time_limit(10 * 60);

        $supports = $repo->findSupportsOfServiceToExport($search, $this->serviceId);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('hotel_supports');
        }

        return (new HotelSupportPersonExport())->exportData($supports);
    }
}
