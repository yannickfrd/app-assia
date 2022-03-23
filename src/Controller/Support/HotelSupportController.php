<?php

declare(strict_types=1);

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

final class HotelSupportController extends AbstractController
{
    /**
     * Liste des suivis AVDL.
     *
     * @Route("/hotel-supports", name="hotel_supports", methods="GET|POST")
     */
    public function viewListHotelSupports(Request $request, Pagination $pagination, SupportPersonRepository $supportPersonRepo): Response
    {
        $form = $this->createForm(HotelSupportSearchType::class, $search = new HotelSupportSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search, $supportPersonRepo);
        }

        return $this->render('app/support/hotel_support/hotel_supports_index.html.twig', [
            'form' => $form->createView(),
            'supports' => $pagination->paginate(
                $supportPersonRepo->findHotelSupportsQuery($search),
                $request
            ),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(HotelSupportSearch $search, SupportPersonRepository $supportPersonRepo)
    {
        $supports = $supportPersonRepo->findSupportsOfServiceToExport($search, Service::SERVICE_TYPE_HOTEL);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('hotel_supports');
        }

        return (new HotelSupportPersonExport())->exportData($supports);
    }
}
