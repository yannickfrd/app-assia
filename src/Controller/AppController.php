<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\Service;
use App\Entity\SubService;
use App\Form\Model\OccupancySearch;
use App\Form\Model\SupportsByUserSearch;
use App\Form\OccupancySearchType;
use App\Form\SupportsByUserSearchType;
use App\Repository\NoteRepository;
use App\Repository\RdvRepository;
use App\Repository\SupportGroupRepository;
use App\Service\Indicators\IndicatorsService;
use App\Service\Indicators\OccupancyIndicators;
use App\Service\Indicators\SupportsByUserIndicators;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    protected $repoSupportGroup;
    protected $repoNote;
    protected $repoRdv;

    public function __construct(
        SupportGroupRepository $repoSupportGroup,
        NoteRepository $repoNote,
        RdvRepository $repoRdv)
    {
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoNote = $repoNote;
        $this->repoRdv = $repoRdv;
    }

    /**
     * @Route("/home", name="home", methods="GET")
     * @Route("/")
     * @IsGranted("ROLE_USER")
     */
    public function home(IndicatorsService $indicators): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->dashboardAdmin($indicators);
        }

        return $this->dashboardSocialWorker();
    }

    /**
     * @Route("/admin", name="admin", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function admin(): Response
    {
        return $this->render('app/admin/admin.html.twig');
    }

    /**
     * @Route("/managing", name="managing", methods="GET")
     */
    public function managing(): Response
    {
        return $this->render('app/managing/managing.html.twig');
    }

    protected function dashboardSocialWorker()
    {
        return $this->render('app/home/home.html.twig', [
            'supports' => $this->repoSupportGroup->findAllSupportsFromUser($this->getUser()),
            'notes' => $this->repoNote->findAllNotesFromUser($this->getUser(), 10),
            'rdvs' => $this->repoRdv->findAllRdvsFromUser($this->getUser(), 10),
        ]);
    }

    protected function dashboardAdmin(IndicatorsService $indicators)
    {
        return $this->render('app/home/dashboardAdmin.html.twig', [
            'indicators' => $this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getIndicators() : null,
            'servicesIndicators' => $indicators->getServicesIndicators(),
            'notes' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $this->repoNote->findAllNotesFromUser($this->getUser(), 10) : null,
            'rdvs' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $this->repoRdv->findAllRdvsFromUser($this->getUser(), 10) : null,
        ]);
    }

    /**
     * Tableau de bord du/des services.
     *
     * @Route("/dashboard/supports_by_user", name="supports_by_user", methods="GET")
     */
    public function showSupportsByUser(SupportsByUserIndicators $indicators, SupportsByUserSearch $search, Request $request): Response
    {
        $form = ($this->createForm(SupportsByUserSearchType::class, $search))
            ->handleRequest($request);

        return $this->render('app/dashboard/supportsByUser.html.twig', [
            'form' => $form->createView(),
            'datas' => $form->isSubmitted() || false == $this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getSupportsbyDevice($search) : null,
        ]);
    }

    /**
     * Taux d'occupation des dispositifs.
     *
     * @Route("/occupancy/devices", name="occupancy_devices", methods="GET|POST")
     * @Route("/occupancy/service/{id}/devices", name="occupancy_service_devices", methods="GET|POST")
     */
    public function showOccupancyByDevice(Service $service = null, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('today');
        $search = (new OccupancySearch())
            ->setStart((new \DateTime('today'))->modify('-1 day'));

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyByDevice.html.twig', [
            'service' => $service,
            'start' => $start,
            'end' => $end,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateByDevice($start, $end, $service),
        ]);
    }

    /**
     * Taux d'occupation des services.
     *
     * @Route("/occupancy/services", name="occupancy_services", methods="GET|POST")
     * @Route("/occupancy/device/{id}/services", name="occupancy_device_services", methods="GET|POST")
     */
    public function showOccupancyByService(Device $device = null, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('today');
        $search = (new OccupancySearch())
            ->setStart((new \DateTime('today'))->modify('-1 day'));

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyByService.html.twig', [
            'device' => $device,
            'start' => $start,
            'end' => $end,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateByService($start, $end, $device),
        ]);
    }

    /**
     * Taux d'occupation des services.
     *
     * @Route("/occupancy/service/{id}/sub_services", name="occupancy_sub_services", methods="GET|POST")
     */
    public function showOccupancyBySubService(Service $service, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('today');
        $search = (new OccupancySearch())
            ->setStart((new \DateTime('today'))->modify('-1 day'));

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyBySubService.html.twig', [
            'service' => $service,
            'start' => $start,
            'end' => $end,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateBySubService($start, $end, $service),
        ]);
    }

    /**
     * Taux d'occupation des groupes de place.
     *
     * @Route("/occupancy/service/{id}/accommodations", name="occupancy_service_accommodations", methods="GET|POST")
     * @Route("/occupancy/accommodations", name="occupancy_accommodations", methods="GET|POST")
     */
    public function showOccupancyServiceByAccommodation(Service $service = null, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('today');

        $search = $this->getOccupancySearch($request);

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyByAccommodation.html.twig', [
            'service' => $service,
            'start' => $start,
            'end' => $end,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateByAccommodation($start, $end, $service),
        ]);
    }

    /**
     * Taux d'occupation des groupes de place.
     *
     * @Route("/occupancy/sub_services/{id}/accommodations", name="occupancy_sub_service_accommodations", methods="GET|POST")
     */
    public function showOccupancySubServiceByAccommodation(SubService $subService, Request $request, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('today');

        $search = $this->getOccupancySearch($request);

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancySubServiceByAccommodation.html.twig', [
            'subService' => $subService,
            'start' => $start,
            'end' => $end,
            'form' => $form->createView(),
            'datas' => $occupancyIndicators->getOccupancyRateByAccommodation($start, $end, null, $subService),
        ]);
    }

    protected function getOccupancySearch(Request $request)
    {
        $search = new OccupancySearch();

        if ($request->query->get('start') && $request->query->get('end')) {
            $search->setStart(new \DateTime($request->query->get('start')))
                ->setEnd(new \DateTime($request->query->get('end')));
        } else {
            $search->setStart((new \DateTime('today'))->modify('-1 day'));
        }

        return $search;
    }
}
