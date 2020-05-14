<?php

namespace App\Controller;

use App\Entity\Device;
use App\Entity\Service;
use App\Service\Indicators;
use App\Service\OccupancyRate;
use App\Form\OccupancySearchType;
use App\Repository\RdvRepository;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Form\Model\OccupancySearch;
use App\Repository\PersonRepository;
use App\Repository\DocumentRepository;
use App\Repository\GroupPeopleRepository;
use App\Repository\SupportGroupRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    protected $repoUser;
    protected $repoPerson;
    protected $repoGroupPeople;
    protected $repoSupport;
    protected $repoNote;
    protected $repoRdv;
    protected $repoDocument;

    public function __construct(PersonRepository $repoPerson, GroupPeopleRepository $repoGroupPeople, UserRepository $repoUser, SupportGroupRepository $repoSupport, NoteRepository $repoNote, RdvRepository $repoRdv, DocumentRepository $repoDocument)
    {
        $this->repoUser = $repoUser;
        $this->repoPerson = $repoPerson;
        $this->repoGroupPeople = $repoGroupPeople;
        $this->repoSupport = $repoSupport;
        $this->repoNote = $repoNote;
        $this->repoRdv = $repoRdv;
        $this->repoDocument = $repoDocument;
    }

    /**
     * @Route("/home", name="home", methods="GET")
     * @Route("/")
     * @IsGranted("ROLE_USER")
     */
    public function home(): Response
    {
        $cache = new FilesystemAdapter();

        if (1 == $this->getUser()->getStatus()) {
            return $this->dashboardSocialWorker($cache);
        }
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->dashboardAdmin($cache);
        }

        return $this->dashboardSocialWorker($cache);
    }

    protected function dashboardSocialWorker($cache)
    {
        $userSupports = $cache->getItem('stats.user'.$this->getUser()->getId().'_supports');

        if (!$userSupports->isHit()) {
            $userSupports->set($this->repoSupport->findAllSupportsFromUser($this->getUser()));
            $userSupports->expiresAfter(2 * 60);  // 5 * 60 seconds
            $cache->save($userSupports);
        }

        return $this->render('app/home/home.html.twig', [
            'supports' => $userSupports->get(),
            'notes' => $this->repoNote->findAllNotesFromUser($this->getUser(), 10),
            'rdvs' => $this->repoRdv->findAllRdvsFromUser($this->getUser(), 10),
        ]);
    }

    protected function dashboardAdmin($cache)
    {
        $indicators = $cache->getItem('stats.indicators');

        if (!$indicators->isHit()) {
            $datas = [];
            $datas['Nombre de personnes'] = (int) $this->repoPerson->countAllPeople();
            $datas['Nombre de groupes'] = (int) $this->repoGroupPeople->countAllGroups();
            $datas['Nombre de suivis'] = (int) $this->repoSupport->countAllSupports();
            $datas['Nombre de suivis en cours'] = (int) $this->repoSupport->countAllSupports(['status' => 2]);
            $datas['Nombre de notes'] = (int) $this->repoNote->countAllNotes();
            $datas['Nombre de RDVs'] = (int) $this->repoRdv->countAllRdvs();
            $datas['Nombre de documents'] = $this->repoDocument->countAllDocuments().' ('.round($this->repoDocument->sumSizeAllDocuments() / 1024 / 1024).' Mo.)';

            $indicators->set($datas);
            $indicators->expiresAfter(5 * 60);  // 5 * 60 seconds
            $cache->save($indicators);
        }

        $usersIndicators = $cache->getItem('stats.users_indicators');

        if (!$usersIndicators->isHit()) {
            $users = [];

            /** @var User $user */
            foreach ($this->repoUser->findUsers(['status' => 1]) as $user) {
                $users[] = [
                    'id' => $user->getId(),
                    'name' => $user->getFullname(),
                    'activeSupports' => (int) $this->repoSupport->countAllSupports([
                        'user' => $user,
                        'status' => 2,
                    ]),
                    'notes' => (int) $this->repoNote->countAllNotes(['user' => $user]),
                    'rdvs' => (int) $this->repoRdv->countAllRdvs(['user' => $user]),
                    'documents' => (int) $this->repoDocument->countAllDocuments(['user' => $user]),
                ];
            }
            $usersIndicators->set($users);
            $usersIndicators->expiresAfter(5 * 60);  // 5 * 60 seconds
            $cache->save($usersIndicators);
        }

        return $this->render('app/home/dashboard.html.twig', [
            'datas' => $indicators->get(),
            'users' => $usersIndicators->get(),
        ]);
    }

    /**
     * Tableau de bord du/des services.
     *
     * @Route("/dashboard/service", name="dashboard_service", methods="GET")
     */
    public function showServiceDashboard(Indicators $indicators): Response
    {
        $cache = new FilesystemAdapter();

        $cacheStatsService = $cache->getItem('stats.service'.$this->getUser()->getId());

        if (!$cacheStatsService->isHit()) {
            $cacheStatsService->set($indicators->getSupportsbyDevice());
            $cacheStatsService->expiresAfter(2 * 60);
            $cache->save($cacheStatsService);
        }

        $statsService = $cacheStatsService->get();

        return $this->render('app/dashboard/dashboardService.html.twig', [
            'devices' => $statsService['devices'],
            'dataUsers' => $statsService['dataUsers'],
            'nbSupports' => $statsService['nbSupports'],
            'sumCoeffSupports' => $statsService['sumCoeffSupports'],
        ]);
    }

    /**
     * Taux d'occupation des dispositifs.
     *
     * @Route("/occupancy/devices", name="occupancy_devices", methods="GET|POST")
     */
    public function showOccupancyByDevice(Request $request, OccupancySearch $search = null, OccupancyRate $occupancyRate): Response
    {
        $today = new \DateTime('midnight');
        $search = (new OccupancySearch())
            ->setStart(new \DateTime($today->format('Y').'-01-01'));

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyByDevice.html.twig', [
            'start' => $start,
            'end' => $end,
            'form' => $form->createView(),
            'datas' => $occupancyRate->getOccupancyRateByDevice($start, $end),
        ]);
    }

    /**
     * Taux d'occupation des services.
     *
     * @Route("/occupancy/services", name="occupancy_services", methods="GET|POST")
     * @Route("/occupancy/device/{id}/services", name="occupancy_device_services", methods="GET|POST")
     */
    public function showOccupancyByService(Device $device = null, Request $request, OccupancySearch $search = null, OccupancyRate $occupancyRate): Response
    {
        $today = new \DateTime('midnight');
        $search = (new OccupancySearch())
            ->setStart(new \DateTime($today->format('Y').'-01-01'));

        $form = ($this->createForm(OccupancySearchType::class, $search))
            ->handleRequest($request);

        $start = $search->getStart() ?? new \DateTime($today->format('Y').'-01-01');
        $end = $search->getEnd() ?? $today;

        return $this->render('app/dashboard/occupancyByService.html.twig', [
            'device' => $device ?? null,
            'start' => $start,
            'end' => $end,
            'form' => $form->createView(),
            'datas' => $occupancyRate->getOccupancyRateByService($start, $end, $device),
        ]);
    }

    /**
     * Taux d'occupation des groupes de place.
     *
     * @Route("/occupancy/service/{id}/accommodations", name="occupancy_service_accommodations", methods="GET|POST")
     * @Route("/occupancy/accommodations", name="occupancy_accommodations", methods="GET|POST")
     */
    public function showOccupancyByAccommodation(Service $service = null, Request $request, OccupancySearch $search = null, OccupancyRate $occupancyRate): Response
    {
        $today = new \DateTime('midnight');
        $search = new OccupancySearch();

        if ($request->query->get('start') and $request->query->get('end')) {
            $search->setStart(new \DateTime($request->query->get('start')))
                ->setEnd(new \DateTime($request->query->get('end')));
        } else {
            $search->setStart(new \DateTime($today->format('Y').'-01-01'));
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
            'datas' => $occupancyRate->getOccupancyRateByAccommodation($start, $end, $service),
        ]);
    }
}
