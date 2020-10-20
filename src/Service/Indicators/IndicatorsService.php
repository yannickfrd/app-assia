<?php

namespace App\Service\Indicators;

use App\Entity\Device;
use App\Entity\Indicator;
use App\Entity\Service;
use App\Entity\SubService;
use App\Entity\SupportGroup;
use App\Entity\User;
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
use App\Service\CacheService;
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

    protected $cacheService;

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

        $this->cacheService = new CacheService();
    }

    public function getIndicators()
    {
        $key = Indicator::CACHE_KEY;

        return $this->cacheService->find($key) ?? $this->cacheService->cache($key, $this->getDatasIndicators(), 60 * 60); // 60 minutes
    }

    public function getServicesIndicators()
    {
        $services = [];

        foreach ($this->repoService->findServicesAndSubServicesOfUser($this->security->getUser()) as $service) {
            $key = Service::CACHE_INDICATORS_KEY.$service->getId();
            $services[$service->getId()] = $this->cacheService->find($key) ?? $this->cacheService->cache($key, $this->getServiceDatas($service), 60 * 60); // 60 minutes
        }

        return $services;
    }

    protected function getSubServicesIndicators(Service $service)
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
                $subServices[$subService->getId()] = $this->getSubServiceDatas($subService, $criteria, $nbActiveSupportsGroups);
            }
        }

        return $subServices;
    }

    protected function getDevicesIndicators(Service $service)
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
                $devices[$device->getId()] = $this->getDeviceDatas($device, $criteria, $nbActiveSupportsGroups);
            }
        }

        return $devices;
    }

    public function getUsersIndicators()
    {
        $key = User::CACHE_INDICATORS_KEY;

        $item = $this->cacheService->find($key);

        if ($item) {
            return $item;
        }

        $users = [];
        foreach ($this->repoUser->findUsers(['status' => 1]) as $user) {
            $users[] = $this->getUserDatas($user);
        }

        return $this->cacheService->cache($key, $users, 60 * 60); // 60 minutes
    }

    public function createIndicator(\DateTime $date): Indicator
    {
        $criteriaByCreation = [
            'filterDateBy' => 'createdAt',
            'startDate' => $startDate = (clone $date),
            'endDate' => (clone $startDate)->modify('+1 day'),
        ];

        $criteriaByUpdate = $criteriaByCreation;
        $criteriaByUpdate['filterDateBy'] = 'updatedAt';

        $indicator = (new Indicator())
            ->setNbCreatedPeople($this->repoPerson->countPeople($criteriaByCreation))
            ->setNbCreatedGroups($this->repoGroupPeople->countGroups($criteriaByCreation))
            ->setNbCreatedSupportsGroup($this->repoSupportGroup->countSupports($criteriaByCreation))
            ->setNbUpdatedSupportsGroup($this->repoSupportGroup->countSupports($criteriaByUpdate))
            ->setNbCreatedSupportsPeople($this->repoSupportPerson->countSupportPeople($criteriaByCreation))
            ->setNbCreatedEvaluations($this->repoEvaluation->countEvaluations($criteriaByCreation))
            ->setNbCreatedNotes($this->repoNote->countNotes($criteriaByCreation))
            ->setNbUpdatedNotes($this->repoNote->countNotes($criteriaByUpdate))
            ->setNbCreatedRdvs($this->repoRdv->countRdvs($criteriaByCreation))
            ->setNbCreatedDocuments($this->repoDocument->countDocuments($criteriaByCreation))
            ->setNbCreatedContributions($this->repoContribution->countContributions($criteriaByCreation))
            ->setNbConnections($this->repoConnection->countConnections($criteriaByCreation))
            ->setDate($startDate);

        return $indicator;
    }

    protected function getDatasIndicators()
    {
        $totay = new \DateTime('today');
        $criteriaByCreation = [
            'filterDateBy' => 'createdAt',
            'startDate' => $totay,
        ];
        $criteriaByUpdate = $criteriaByCreation;
        $criteriaByUpdate['filterDateBy'] = 'updatedAt';

        $indicator = $this->repoIndicator->findOneBy(['date' => new \DateTime('yesterday')]);
        $yesterdayIndicator = $indicator ?? $this->createIndicator($totay);

        return [
            'Nombre de personnes créées' => [
                'all' => $this->repoPerson->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedPeople(),
                'today' => $this->repoPerson->countPeople($criteriaByCreation),
            ],
            'Nombre de groupes créés' => [
                'all' => $this->repoGroupPeople->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedGroups(),
                'today' => $this->repoGroupPeople->countGroups($criteriaByCreation),
            ],
            'Nombre de suivis créés' => [
                'all' => $allSupports = $this->repoSupportGroup->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedSupportsGroup(),
                'today' => $this->repoSupportGroup->countSupports($criteriaByCreation),
            ],
            'Nombre de suivis mis à jour' => [
                'all' => $allSupports,
                'yesterday' => $yesterdayIndicator->getNbUpdatedNotes(),
                'today' => $this->repoSupportGroup->countSupports($criteriaByUpdate),
            ],
            'Nombre de suivis en cours' => $this->repoSupportGroup->count([
                'status' => SupportGroup::STATUS_IN_PROGRESS,
            ]),
            'Nombre de notes créées' => [
                'all' => $allNotes = $this->repoNote->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedNotes(),
                'today' => $this->repoNote->countNotes($criteriaByCreation),
            ],
            'Nombre de notes mises à jour' => [
                'all' => $allNotes,
                'yesterday' => $yesterdayIndicator->getNbUpdatedNotes(),
                'today' => $this->repoNote->countNotes($criteriaByUpdate),
            ],
            'Nombre de RDVs créés' => [
                'all' => $this->repoRdv->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedRdvs(),
                'today' => $this->repoRdv->countRdvs($criteriaByCreation),
            ],
            'Nombre de documents créés' => [
                'all' => $this->repoDocument->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedDocuments(),
                'today' => $this->repoDocument->countDocuments($criteriaByCreation),
            ],
            'Nombre de paiements créés' => [
                'all' => $this->repoContribution->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedContributions(),
                'today' => $this->repoContribution->countContributions($criteriaByCreation),
            ],
            'Nombre de connexions' => [
                'all' => $this->repoConnection->count([]),
                'yesterday' => $yesterdayIndicator->getNbConnections(),
                'today' => $this->repoConnection->countConnections($criteriaByCreation),
            ],
        ];
    }

    protected function getServiceDatas(Service $service)
    {
        $criteria = [
            'service' => $service,
            'status' => SupportGroup::STATUS_IN_PROGRESS,
        ];

        $nbActiveSupportsGroups = $this->repoSupportGroup->count($criteria);

        return [
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
    }

    protected function getSubServiceDatas(SubService $subService, array $criteria, int $nbActiveSupportsGroups)
    {
        return [
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

    protected function getDeviceDatas(Device $device, array $criteria, int $nbActiveSupportsGroups)
    {
        return [
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

    protected function getUserDatas(User $user)
    {
        return [
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
}
