<?php

namespace App\Service\Indicators;

use App\Entity\Indicator;
use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\ContributionRepository;
use App\Repository\DocumentRepository;
use App\Repository\EvaluationGroupRepository;
use App\Repository\GroupPeopleRepository;
use App\Repository\IndicatorRepository;
use App\Repository\NoteRepository;
use App\Repository\PersonRepository;
use App\Repository\RdvRepository;
use App\Repository\ServiceRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use App\Repository\UserConnectionRepository;
use App\Repository\UserRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Core\Security;

class IndicatorsService
{
    protected $security;

    protected $repoIndicator;
    protected $repoUser;
    protected $repoService;
    protected $repoPerson;
    protected $repoGroupPeople;
    protected $repoSupportGroup;
    protected $repoSupportPerson;
    protected $repoEvaluation;
    protected $repoNote;
    protected $repoRdv;
    protected $repoDocument;
    protected $repoContribution;
    protected $repoConnection;

    protected $cache;

    public function __construct(
        Security $security,
        IndicatorRepository $repoIndicator,
        UserRepository $repoUser,
        ServiceRepository $repoService,
        PersonRepository $repoPerson,
        GroupPeopleRepository $repoGroupPeople,
        SupportGroupRepository $repoSupportGroup,
        SupportPersonRepository $repoSupportPerson,
        EvaluationGroupRepository $repoEvaluation,
        NoteRepository $repoNote,
        RdvRepository $repoRdv,
        DocumentRepository $repoDocument,
        ContributionRepository $repoContribution,
        UserConnectionRepository $repoConnection)
    {
        $this->security = $security;

        $this->repoIndicator = $repoIndicator;
        $this->repoUser = $repoUser;
        $this->repoService = $repoService;
        $this->repoPerson = $repoPerson;
        $this->repoGroupPeople = $repoGroupPeople;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoSupportPerson = $repoSupportPerson;
        $this->repoEvaluation = $repoEvaluation;
        $this->repoNote = $repoNote;
        $this->repoRdv = $repoRdv;
        $this->repoDocument = $repoDocument;
        $this->repoContribution = $repoContribution;
        $this->repoConnection = $repoConnection;

        $this->cache = new FilesystemAdapter();
    }

    public function getIndicators()
    {
        $indicators = $this->cache->getItem('indicators');

        $criteria = ['startDate' => new \DateTime('today')];

        if (!$indicators->isHit()) {
            $datas = [];
            $datas['Nombre de personnes'] = [
                'all' => $this->repoPerson->count([]),
                'today' => $this->repoPerson->countPeople($criteria),
            ];
            $datas['Nombre de groupes'] = [
                'all' => $this->repoGroupPeople->count([]),
                'today' => $this->repoGroupPeople->countGroups($criteria),
            ];
            $datas['Nombre de suivis'] = [
                'all' => $this->repoSupportGroup->count([]),
                'today' => $this->repoSupportGroup->countSupports($criteria),
            ];
            $datas['Nombre de suivis en cours'] = $this->repoSupportGroup->count(['status' => SupportGroup::STATUS_IN_PROGRESS]);
            $datas['Nombre de notes'] = [
                'all' => $this->repoNote->count([]),
                'today' => $this->repoNote->countNotes($criteria),
            ];
            $datas['Nombre de RDVs'] = [
                'all' => $this->repoRdv->count([]),
                'today' => $this->repoRdv->countRdvs($criteria),
            ];
            $datas['Nombre de documents'] = [
                'all' => $this->repoDocument->count([]),
                'today' => $this->repoDocument->countDocuments($criteria),
            ];
            $datas['Nombre de paiements'] = [
                'all' => $this->repoContribution->count([]),
                'today' => $this->repoContribution->countContributions($criteria),
            ];

            $indicators->set($datas);
            $indicators->expiresAfter(60 * 60); // 60mn
            $this->cache->save($indicators);
        }

        return $indicators->get();
    }

    public function getServicesIndicators()
    {
        $services = [];

        foreach ($this->repoService->findServicesAndSubServicesOfUser($this->security->getUser()) as $service) {
            $indicatorsService = $this->cache->getItem('indicators_service_'.$service->getId());

            if (!$indicatorsService->isHit()) {
                $criteria = [
                    'service' => $service,
                    'status' => SupportGroup::STATUS_IN_PROGRESS,
                ];

                $nbActiveSupportsGroups = $this->repoSupportGroup->count($criteria);

                $serviceDatas = [
                    'id' => $service->getId(),
                    'name' => $service->getName(),
                    'activeSupportsGroups' => $nbActiveSupportsGroups,
                    'activeSupportsPeople' => $this->repoSupportPerson->countSupportPeople($criteria),
                    'avgTimeSupport' => $this->repoSupportGroup->avgTimeSupport($criteria),
                    'avgSupportsByUser' => $this->repoSupportGroup->avgSupportsByUser($criteria),
                    'siaoRequest' => $this->repoSupportGroup->countSupports([
                        'service' => $service,
                        'status' => SupportGroup::STATUS_IN_PROGRESS,
                        'siaoRequest' => Choices::YES,
                    ]),
                    'socialHousingRequest' => $this->repoSupportGroup->countSupports([
                        'service' => $service,
                        'status' => SupportGroup::STATUS_IN_PROGRESS,
                        'socialHousingRequest' => Choices::YES,
                    ]),
                    // 'notes' => $this->repoNote->countNotes($criteria),
                    // 'rdvs' => $this->repoRdv->countRdvs($criteria),
                    // 'documents' => $this->repoDocument->countDocuments($criteria),
                    // 'contributions' => $this->repoContribution->countContributions($criteria),
                    'devices' => $this->getDevicesIndicators($service),
                    'subServices' => $this->getSubServicesIndicators($service),
                ];
                $indicatorsService->set($serviceDatas);
                $indicatorsService->expiresAfter(10 * 60); // 10mn
                $this->cache->save($indicatorsService);
            }
            $services[$service->getId()] = $indicatorsService->get();
        }

        return $services;
    }

    public function getSubServicesIndicators(Service $service)
    {
        if ($service->getSubServices()->count() <= 1) {
            return null;
        }
        $subServices = [];

        foreach ($service->getSubServices() as $subService) {
            $criteria = [
                'subService' => $subService,
                'status' => SupportGroup::STATUS_IN_PROGRESS,
            ];

            $nbActiveSupportsGroups = $this->repoSupportGroup->count($criteria);

            if ((int) $nbActiveSupportsGroups > 0) {
                $subServices[] = [
                    'name' => $subService->getName(),
                    'activeSupportsGroups' => $nbActiveSupportsGroups,
                    'activeSupportsPeople' => $this->repoSupportPerson->countSupportPeople($criteria),
                    'avgTimeSupport' => $this->repoSupportGroup->avgTimeSupport($criteria),
                    'avgSupportsByUser' => $this->repoSupportGroup->avgSupportsByUser($criteria),
                    'siaoRequest' => $this->repoSupportGroup->countSupports([
                        'subService' => $subService,
                        'status' => SupportGroup::STATUS_IN_PROGRESS,
                        'siaoRequest' => Choices::YES,
                    ]),
                    'socialHousingRequest' => $this->repoSupportGroup->countSupports([
                        'subService' => $subService,
                        'status' => SupportGroup::STATUS_IN_PROGRESS,
                        'socialHousingRequest' => Choices::YES,
                    ]),
                    // 'notes' => $this->repoNote->countNotes($criteria),
                    // 'rdvs' => $this->repoRdv->countRdvs($criteria),
                    // 'documents' => $this->repoDocument->countDocuments($criteria),
                    // 'contributions' => $this->repoContribution->countContributions($criteria),
                ];
            }
        }

        return $subServices;
    }

    public function getDevicesIndicators(Service $service)
    {
        if ($service->getDevices()->count() <= 1) {
            return null;
        }

        $devices = [];

        foreach ($service->getDevices() as $device) {
            $criteria = [
                'device' => $device,
                'status' => SupportGroup::STATUS_IN_PROGRESS,
            ];

            $nbActiveSupportsGroups = $this->repoSupportGroup->count($criteria);

            if ((int) $nbActiveSupportsGroups > 0) {
                $devices[] = [
                    'name' => $device->getName(),
                    'activeSupportsGroups' => $nbActiveSupportsGroups,
                    'activeSupportsPeople' => $this->repoSupportPerson->countSupportPeople($criteria),
                    'avgTimeSupport' => $this->repoSupportGroup->avgTimeSupport($criteria),
                    'avgSupportsByUser' => $this->repoSupportGroup->avgSupportsByUser($criteria),
                    'siaoRequest' => $this->repoSupportGroup->countSupports([
                        'device' => $device,
                        'status' => SupportGroup::STATUS_IN_PROGRESS,
                        'siaoRequest' => Choices::YES,
                    ]),
                    'socialHousingRequest' => $this->repoSupportGroup->countSupports([
                        'device' => $device,
                        'status' => SupportGroup::STATUS_IN_PROGRESS,
                        'socialHousingRequest' => Choices::YES,
                    ]),
                    // 'notes' => $this->repoNote->countNotes($criteria),
                    // 'rdvs' => $this->repoRdv->countRdvs($criteria),
                    // 'documents' => $this->repoDocument->countDocuments($criteria),
                    // 'contributions' => $this->repoContribution->countContributions($criteria),
                ];
            }
        }

        return $devices;
    }

    public function getUsersIndicators(FilesystemAdapter $cache)
    {
        $usersIndicators = $cache->getItem('indicators_users');

        if (!$usersIndicators->isHit()) {
            $users = [];

            /** @var User $user */
            foreach ($this->repoUser->findUsers(['status' => 1]) as $user) {
                $users[] = [
                    'id' => $user->getId(),
                    'name' => $user->getFullname(),
                    'activeSupports' => $this->repoSupportGroup->count([
                        'referent' => $user,
                        'status' => SupportGroup::STATUS_IN_PROGRESS,
                    ]),
                    'notes' => $this->repoNote->count(['createdBy' => $user]),
                    'rdvs' => $this->repoRdv->count(['createdBy' => $user]),
                    'documents' => $this->repoDocument->count(['createdBy' => $user]),
                    'contributions' => $this->repoContribution->count(['createdBy' => $user]),
                ];
            }
            $usersIndicators->set($users);
            $usersIndicators->expiresAfter(5 * 60); // 5mn
            $cache->save($usersIndicators);
        }

        return $usersIndicators->get();
    }

    public function createIndicator(\DateTime $date): Indicator
    {
        $startDate = (clone $date);

        $criteria = [
            'startDate' => $startDate,
            'endDate' => (clone $startDate)->modify('+1 day'),
        ];

        $indicator = (new Indicator())
            ->setNbPeople($this->repoPerson->countPeople($criteria))
            ->setNbGroups($this->repoGroupPeople->countGroups($criteria))
            ->setNbSupportsGroup($this->repoSupportGroup->countSupports($criteria))
            ->setNbSupportsPeople($this->repoSupportPerson->countSupportPeople($criteria))
            ->setNbEvaluations($this->repoEvaluation->countEvaluations($criteria))
            ->setNbNotes($this->repoNote->countNotes($criteria))
            ->setNbRdvs($this->repoRdv->countRdvs($criteria))
            ->setNbDocuments($this->repoDocument->countDocuments($criteria))
            ->setNbContributions($this->repoContribution->countContributions($criteria))
            ->setNbConnections($this->repoConnection->countConnections($criteria))
            ->setDate($startDate);

        return $indicator;
    }
}
