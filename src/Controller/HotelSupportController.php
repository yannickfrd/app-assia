<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Export\HotelSupportPersonExport;
use App\Form\HotelSupport\HotelSupportSearchType;
use App\Form\Model\HotelSupportSearch;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HotelSupportController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repoSupportGroup;
    private $repoSupportPerson;
    private $serviceId;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, SupportPersonRepository $repoSupportPerson)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoSupportPerson = $repoSupportPerson;
        $this->serviceId = Service::SERVICE_PASH_ID;
    }

    /**
     * Liste des suivis AVDL.
     *
     * @Route("/hotel-supports", name="hotel_supports", methods="GET|POST")
     */
    public function viewListHotelSupports(Request $request, Pagination $pagination): Response
    {
        $search = (new HotelSupportSearch())->setStatus([SupportGroup::STATUS_IN_PROGRESS]);

        $form = ($this->createForm(HotelSupportSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/hotelSupport/listHotelSupports.html.twig', [
            'supportGroupSearch' => $search,
            'form' => $form->createView(),
            'supports' => $pagination->paginate(
                $this->repoSupportGroup->findAllHotelSupportsQuery($search, $this->serviceId),
                $request),
        ]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(HotelSupportSearch $search)
    {
        $supports = $this->repoSupportPerson->findSupportsFromServiceToExport($search, $this->serviceId);

        if (!$supports) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('hotel_supports');
        }

        return (new HotelSupportPersonExport())->exportData($supports);
    }
}
