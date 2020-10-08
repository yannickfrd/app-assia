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
use App\Repository\ContributionRepository;
use App\Repository\DocumentRepository;
use App\Repository\GroupPeopleRepository;
use App\Repository\NoteRepository;
use App\Repository\PersonRepository;
use App\Repository\RdvRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\UserRepository;
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
    protected $repoUser;
    protected $repoPerson;
    protected $repoGroupPeople;
    protected $repoSupport;
    protected $repoNote;
    protected $repoRdv;
    protected $repoDocument;
    protected $repoContribution;

    public function __construct(
        UserRepository $repoUser,
        PersonRepository $repoPerson,
        GroupPeopleRepository $repoGroupPeople,
        SupportGroupRepository $repoSupport,
        NoteRepository $repoNote,
        RdvRepository $repoRdv,
        DocumentRepository $repoDocument,
        ContributionRepository $repoContribution)
    {
        $this->repoUser = $repoUser;
        $this->repoPerson = $repoPerson;
        $this->repoGroupPeople = $repoGroupPeople;
        $this->repoSupport = $repoSupport;
        $this->repoNote = $repoNote;
        $this->repoRdv = $repoRdv;
        $this->repoDocument = $repoDocument;
        $this->repoContribution = $repoContribution;
    }

    /**
     * @Route("/home", name="home", methods="GET")
     * @Route("/")
     * @IsGranted("ROLE_USER")
     */
    public function home(): Response
    {
        $cache = new FilesystemAdapter();

        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->dashboardAdmin($cache);
        }

        return $this->dashboardSocialWorker($cache);
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
            'supports' => $this->repoSupport->findAllSupportsFromUser($this->getUser()),
            'notes' => $this->repoNote->findAllNotesFromUser($this->getUser(), 10),
            'rdvs' => $this->repoRdv->findAllRdvsFromUser($this->getUser(), 10),
        ]);
    }

    protected function dashboardAdmin(FilesystemAdapter $cache)
    {
        return $this->render('app/home/dashboardAdmin.html.twig', [
            'datas' => $this->getIndicators($cache),
            // 'users' => $this->getUsersIndicators($cache),
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
    public function showOccupancyByDevice(Service $service = null, Request $request, OccupancySearch $search = null, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('midnight');
        $search = (new OccupancySearch())
            ->setStart((new \DateTime('midnight'))->modify('-1 day'));

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyByDevice.html.twig', [
            'service' => $service ?? null,
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
    public function showOccupancyByService(Device $device = null, Request $request, OccupancySearch $search = null, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('midnight');
        $search = (new OccupancySearch())
            ->setStart((new \DateTime('midnight'))->modify('-1 day'));

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyByService.html.twig', [
            'device' => $device ?? null,
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
    public function showOccupancyBySubService(Service $service, Request $request, OccupancySearch $search = null, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('midnight');
        $search = (new OccupancySearch())
            ->setStart((new \DateTime('midnight'))->modify('-1 day'));

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
    public function showOccupancyServiceByAccommodation(Service $service = null, Request $request, OccupancySearch $search = null, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('midnight');
        $search = new OccupancySearch();

        if ($request->query->get('start') && $request->query->get('end')) {
            $search->setStart(new \DateTime($request->query->get('start')))
                ->setEnd(new \DateTime($request->query->get('end')));
        } else {
            $search->setStart((new \DateTime('midnight'))->modify('-1 day'));
        }

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyByAccommodation.html.twig', [
            'service' => $service ?? null,
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
    public function showOccupancySubServiceByAccommodation(SubService $subService, Request $request, OccupancySearch $search = null, OccupancyIndicators $occupancyIndicators): Response
    {
        $today = new \DateTime('midnight');
        $search = new OccupancySearch();

        if ($request->query->get('start') && $request->query->get('end')) {
            $search->setStart(new \DateTime($request->query->get('start')))
                ->setEnd(new \DateTime($request->query->get('end')));
        } else {
            $search->setStart((new \DateTime('midnight'))->modify('-1 day'));
        }

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

    public function cleanString(string $string)
    {
        $string = strtr($string, [
            'à' => 'a',
            'ç' => 'c',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
        ]);
        $string = strtolower($string);
        $string = str_replace(' ', '+', $string);

        return $string;
    }

    protected function getIndicators(FilesystemAdapter $cache)
    {
        $indicators = $cache->getItem('stats.indicators');

        if (!$indicators->isHit()) {
            $datas = [];
            $datas['Nombre de personnes'] = (int) $this->repoPerson->count([]);
            $datas['Nombre de groupes'] = (int) $this->repoGroupPeople->count([]);
            $datas['Nombre de suivis'] = (int) $this->repoSupport->count([]);
            $datas['Nombre de suivis en cours'] = (int) $this->repoSupport->count(['status' => 2]);
            $datas['Nombre de notes'] = (int) $this->repoNote->count([]);
            $datas['Nombre de RDVs'] = (int) $this->repoRdv->count([]);
            $datas['Nombre de documents'] = $this->repoDocument->count([]).' ('.round($this->repoDocument->sumSizeAllDocuments() / 1024 / 1024).' Mo.)';
            $datas['Nombre de paiements'] = (int) $this->repoContribution->count([]);

            $indicators->set($datas);
            $indicators->expiresAfter(5 * 60); // 5mn
            $cache->save($indicators);
        }

        return $indicators->get();
    }

    protected function getUsersIndicators(FilesystemAdapter $cache)
    {
        $usersIndicators = $cache->getItem('stats.users_indicators');

        if (!$usersIndicators->isHit()) {
            $users = [];

            /** @var User $user */
            foreach ($this->repoUser->findUsers(['status' => 1]) as $user) {
                $users[] = [
                    'id' => $user->getId(),
                    'name' => $user->getFullname(),
                    'activeSupports' => (int) $this->repoSupport->count([
                        'referent' => $user,
                        'status' => 2,
                    ]),
                    'notes' => (int) $this->repoNote->count(['createdBy' => $user]),
                    'rdvs' => (int) $this->repoRdv->count(['createdBy' => $user]),
                    'documents' => (int) $this->repoDocument->count(['createdBy' => $user]),
                    'contributions' => (int) $this->repoContribution->count(['createdBy' => $user]),
                ];
            }
            $usersIndicators->set($users);
            $usersIndicators->expiresAfter(5 * 60); // 5mn
            $cache->save($usersIndicators);
        }

        return $usersIndicators->get();
    }
}
