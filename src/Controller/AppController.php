<?php

namespace App\Controller;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Form\Admin\OccupancySearchType;
use App\Form\Admin\SupportsByUserSearchType;
use App\Form\Model\Admin\OccupancySearch;
use App\Form\Model\Support\SupportsByUserSearch;
use App\Service\Indicators\IndicatorsService;
use App\Service\Indicators\OccupancyIndicators;
use App\Service\Indicators\SupportsByUserIndicators;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    protected $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Page d'accueil / Tableau de bord.
     *
     * @Route("/home", name="home", methods="GET")
     * @Route("/")
     * @IsGranted("ROLE_USER")
     */
    public function home(IndicatorsService $indicators): Response
    {
        return $this->render('app/admin/home/dashboard.html.twig', [
            'indicators' => $this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getIndicators() : null,
            'servicesIndicators' => $indicators->getServicesIndicators($indicators->getUserServices($this->getUser())),
            'supports' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getUserSupports($this->getUser()) : null,
            'notes' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getUserNotes($this->getUser()) : null,
            'rdvs' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getUserRdvs($this->getUser()) : null,
        ]);
    }

    /**
     * Page d'administration de l'application.
     *
     * @Route("/admin", name="admin", methods="GET")
     * @IsGranted("ROLE_ADMIN")
     */
    public function admin(): Response
    {
        return $this->render('app/admin/admin.html.twig');
    }

    /**
     * Page de gestion du ou des services.
     *
     * @Route("/managing", name="managing", methods="GET")
     * @IsGranted("ROLE_USER")
     */
    public function managing(): Response
    {
        return $this->render('app/admin/managing/managing.html.twig');
    }

    /**
     * Page de gestion du ou des services.
     *
     * @Route("/admin/cache/clear", name="cache_clear", methods="GET")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function clearCache(): Response
    {
        $this->cache->clear();

        $this->addFlash('success', 'Le cache est vide.');

        return $this->redirectToRoute('home');
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

        return $this->render('app/admin/dashboard/supportsByUser.html.twig', [
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

        return $this->render('app/admin/dashboard/occupancyByDevice.html.twig', [
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

        return $this->render('app/admin/dashboard/occupancyByService.html.twig', [
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

        return $this->render('app/admin/dashboard/occupancyBySubService.html.twig', [
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

        return $this->render('app/admin/dashboard/occupancyByAccommodation.html.twig', [
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

        return $this->render('app/admin/dashboard/occupancySubServiceByAccommodation.html.twig', [
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
