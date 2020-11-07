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
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class IndicatorsService
{
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

    /**
     * Donne les indicateurs généraux de l'application.
     */
    public function getIndicators()
    {
        return $this->cache->get(Indicator::CACHE_KEY, function (CacheItemInterface $item) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 hour'));

            return $this->getDatasIndicators();
        });
    }

    /**
     * Donne les indicateurs des services.
     */
    public function getServicesIndicators(array $services): array
    {
        $datasServices = [];

        foreach ($services as $service) {
            $datasServices[$service->getId()] = $this->cache->get(Service::CACHE_INDICATORS_KEY.$service->getId(), function (CacheItemInterface $item) use ($service) {
                $item->expiresAfter(\DateInterval::createFromDateString('1 hour'));

                return $this->getServiceDatas($service);
            });
        }

        return $datasServices;
    }

    /**
     * Donne les indicateurs des sous-services du service.
     */
    protected function getSubServicesIndicators(Service $service): ?array
    {
        if ($service->getSubServices()->count() <= 1) {
            return null;
        }
        $datasSubServices = [];

        foreach ($service->getSubServices() as $subService) {
            $criteria = [
                'subService' => $subService,
                'status' => SupportGroup::STATUS_IN_PROGRESS,
            ];

            $nbActiveSupportsGroups = $this->repoSupportGroup->count($criteria);

            if ((int) $nbActiveSupportsGroups > 0) {
                $datasSubServices[$subService->getId()] = $this->getSubServiceDatas($subService, $criteria, $nbActiveSupportsGroups);
            }
        }

        return $datasSubServices;
    }

    /**
     * Les indicateurs des dispositifs du service.
     */
    protected function getDevicesIndicators(Service $service): ?array
    {
        if ($service->getDevices()->count() <= 1) {
            return null;
        }

        $datasDevices = [];

        foreach ($service->getDevices() as $device) {
            $criteria = [
                'device' => $device,
                'status' => SupportGroup::STATUS_IN_PROGRESS,
            ];

            $nbActiveSupportsGroups = $this->repoSupportGroup->count($criteria);

            if ((int) $nbActiveSupportsGroups > 0) {
                $datasDevices[$device->getId()] = $this->getDeviceDatas($device, $criteria, $nbActiveSupportsGroups);
            }
        }

        return $datasDevices;
    }

    /**
     * Donne les indicateurs de tous les utilisateurs.
     */
    public function getUsersIndicators(): array
    {
        return $this->cache->get(User::CACHE_INDICATORS_KEY, function (CacheItemInterface $item) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 hour'));

            $users = [];

            foreach ($this->repoUser->findUsers(['status' => 1]) as $user) {
                $users[] = $this->getUserDatas($user);
            }

            return $users;
        });
    }

    /**
     * Enregistre les indicateurs d'activité d'une journée.
     */
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

    /**
     * Donne les indicateurs généraux.
     */
    protected function getDatasIndicators(): array
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
            'Nb. de personnes créées' => [
                'all' => $this->repoPerson->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedPeople(),
                'today' => $this->repoPerson->countPeople($criteriaByCreation),
            ],
            'Nb. de groupes créés' => [
                'all' => $this->repoGroupPeople->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedGroups(),
                'today' => $this->repoGroupPeople->countGroups($criteriaByCreation),
            ],
            'Nb. de suivis créés' => [
                'all' => $allSupports = $this->repoSupportGroup->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedSupportsGroup(),
                'today' => $this->repoSupportGroup->countSupports($criteriaByCreation),
            ],
            'Nb. de suivis mis à jour' => [
                'all' => $allSupports,
                'yesterday' => $yesterdayIndicator->getNbUpdatedNotes(),
                'today' => $this->repoSupportGroup->countSupports($criteriaByUpdate),
            ],
            'Nb. de suivis en cours' => $this->repoSupportGroup->count([
                'status' => SupportGroup::STATUS_IN_PROGRESS,
            ]),
            'Nb. de notes créées' => [
                'all' => $allNotes = $this->repoNote->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedNotes(),
                'today' => $this->repoNote->countNotes($criteriaByCreation),
            ],
            'Nb. de notes mises à jour' => [
                'all' => $allNotes,
                'yesterday' => $yesterdayIndicator->getNbUpdatedNotes(),
                'today' => $this->repoNote->countNotes($criteriaByUpdate),
            ],
            'Nb. de RDVs créés' => [
                'all' => $this->repoRdv->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedRdvs(),
                'today' => $this->repoRdv->countRdvs($criteriaByCreation),
            ],
            'Nb. de documents créés' => [
                'all' => $this->repoDocument->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedDocuments(),
                'today' => $this->repoDocument->countDocuments($criteriaByCreation),
            ],
            'Nb. de paiements créés' => [
                'all' => $this->repoContribution->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedContributions(),
                'today' => $this->repoContribution->countContributions($criteriaByCreation),
            ],
            'Nb. de connexions' => [
                'all' => $this->repoConnection->count([]),
                'yesterday' => $yesterdayIndicator->getNbConnections(),
                'today' => $this->repoConnection->countConnections($criteriaByCreation),
            ],
        ];
    }

    /**
     * Donne les indicateurs d'un service.
     */
    protected function getServiceDatas(Service $service): array
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

    /**
     * Donne les indicateurs d'un sous-service.
     */
    protected function getSubServiceDatas(SubService $subService, array $criteria, int $nbActiveSupportsGroups): array
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

    /**
     * Donne les indicateurs d'un dispositif.
     */
    protected function getDeviceDatas(Device $device, array $criteria, int $nbActiveSupportsGroups): array
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

    /**
     * Donne les indicateurs d'un utilisateur.
     */
    protected function getUserDatas(User $user): array
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

    /**
     * Donne les services de l'utilisateur en cache.
     */
    public function getServices(User $user): ?array
    {
        return $this->cache->get(User::CACHE_USER_SERVICES_KEY.$user->getId(), function (CacheItemInterface $item) use ($user) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $this->repoService->findServicesAndSubServicesOfUser($user);
        });
    }

    /**
     * Donne les suivis de l'utilisateur en cache.
     */
    public function getSupports(User $user): ?array
    {
        return $this->cache->get(User::CACHE_USER_SUPPORTS_KEY.$user->getId(), function (CacheItemInterface $item) use ($user) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            return $this->repoSupportGroup->findAllSupportsFromUser($user);
        });
    }

    /**
     * Donne les notes de l'utilisateur en cache.
     */
    public function getNotes(User $user): ?array
    {
        return $this->cache->get(User::CACHE_USER_NOTES_KEY.$user->getId(), function (CacheItemInterface $item) use ($user) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            $this->repoNote->findAllNotesFromUser($user, 10);
        });
    }

    /**
     * Donne les rdvs de l'utilisateur en cache.
     */
    public function getRdvs(User $user): ?array
    {
        return $this->cache->get(User::CACHE_USER_RDVS_KEY.$user->getId(), function (CacheItemInterface $item) use ($user) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            $this->repoRdv->findAllRdvsFromUser($user, 10);
        });
    }
}
