<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\Service;
use App\Entity\SubService;
use App\Entity\User;
use App\Form\Model\OccupancySearch;
use App\Form\Model\SupportsByUserSearch;
use App\Form\OccupancySearchType;
use App\Form\SupportsByUserSearchType;
use App\Repository\NoteRepository;
use App\Repository\RdvRepository;
use App\Repository\ServiceRepository;
use App\Repository\SupportGroupRepository;
use App\Service\Indicators\IndicatorsService;
use App\Service\Indicators\OccupancyIndicators;
use App\Service\Indicators\SupportsByUserIndicators;
use Psr\Cache\CacheItemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class AppController extends AbstractController
{
    protected $repoService;
    protected $repoSupportGroup;
    protected $repoNote;
    protected $repoRdv;
    protected $cache;

    public function __construct(ServiceRepository $repoService, SupportGroupRepository $repoSupportGroup, NoteRepository $repoNote, RdvRepository $repoRdv)
    {
        $this->repoService = $repoService;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoNote = $repoNote;
        $this->repoRdv = $repoRdv;

        $this->cache = new FilesystemAdapter();
    }

    /**
     * Page d'accueil / Tableau de bord.
     *
     * @Route("/home", name="home", methods="GET")
     * @Route("/")
     * @IsGranted("ROLE_USER")
     */
    public function home(IndicatorsService $indicators, CacheInterface $cache): Response
    {
        return $this->render('app/home/dashboard.html.twig', [
            'indicators' => $this->isGranted('ROLE_SUPER_ADMIN') ? $indicators->getIndicators() : null,
            'servicesIndicators' => $indicators->getServicesIndicators($this->getServices()),
            'supports' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $this->getSupports() : null,
            'notes' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $this->getNotes() : null,
            'rdvs' => !$this->isGranted('ROLE_SUPER_ADMIN') ? $this->getRdvs() : null,
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
        return $this->render('app/managing/managing.html.twig');
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

    /**
     * Donne les services de l'utilisateur en cache.
     */
    protected function getServices(): ?array
    {
        return $this->cache->get(User::CACHE_USER_SERVICES_KEY.$this->getUser()->getId(), function (CacheItemInterface $item) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $this->repoService->findServicesAndSubServicesOfUser($this->getUser());
        });
    }

    /**
     * Donne les suivis de l'utilisateur en cache.
     */
    protected function getSupports(): ?array
    {
        return $this->cache->get(User::CACHE_USER_SUPPORTS_KEY.$this->getUser()->getId(), function (CacheItemInterface $item) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            return $this->repoSupportGroup->findAllSupportsFromUser($this->getUser());
        });
    }

    /**
     * Donne les notes de l'utilisateur en cache.
     */
    protected function getNotes(): ?array
    {
        return $this->cache->get(User::CACHE_USER_NOTES_KEY.$this->getUser()->getId(), function (CacheItemInterface $item) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            $this->repoNote->findAllNotesFromUser($this->getUser(), 10);
        });
    }

    /**
     * Donne les rdvs de l'utilisateur en cache.
     */
    protected function getRdvs(): ?array
    {
        return $this->cache->get(User::CACHE_USER_RDVS_KEY.$this->getUser()->getId(), function (CacheItemInterface $item) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            $this->repoRdv->findAllRdvsFromUser($this->getUser(), 10);
        });
    }
}
