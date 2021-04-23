<?php

namespace App\Service\Indicators;

use App\Entity\Admin\Indicator;
use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\Admin\IndicatorRepository;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\UserConnectionRepository;
use App\Repository\Organization\UserRepository;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\People\PersonRepository;
use App\Repository\Support\ContributionRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\RdvRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Repository\Support\SupportPersonRepository;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class IndicatorsService
{
    protected $indicatorRepo;
    protected $userRepo;
    protected $serviceRepo;
    protected $personRepo;
    protected $peopleGroupRepo;
    protected $supportGroupRepo;
    protected $supportPersonRepo;
    protected $evaluationRepo;
    protected $noteRepo;
    protected $rdvRepo;
    protected $documentRepo;
    protected $contributionRepo;
    protected $ConnectionRepo;

    protected $cache;

    public function __construct(
        IndicatorRepository $indicatorRepo,
        UserRepository $userRepo,
        ServiceRepository $serviceRepo,
        PersonRepository $personRepo,
        PeopleGroupRepository $peopleGroupRepo,
        SupportGroupRepository $supportGroupRepo,
        SupportPersonRepository $supportPersonRepo,
        EvaluationGroupRepository $evaluationRepo,
        NoteRepository $noteRepo,
        RdvRepository $rdvRepo,
        DocumentRepository $documentRepo,
        ContributionRepository $contributionRepo,
        UserConnectionRepository $ConnectionRepo)
    {
        $this->repoIndicator = $indicatorRepo;
        $this->UserRepo = $userRepo;
        $this->serviceRepo = $serviceRepo;
        $this->personRepo = $personRepo;
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->supportPersonRepo = $supportPersonRepo;
        $this->evaluationRepo = $evaluationRepo;
        $this->noteRepo = $noteRepo;
        $this->rdvRepo = $rdvRepo;
        $this->documentRepo = $documentRepo;
        $this->contributionRepo = $contributionRepo;
        $this->ConnectionRepo = $ConnectionRepo;

        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Donne les indicateurs généraux de l'application.
     */
    public function getIndicators()
    {
        return $this->cache->get(Indicator::CACHE_KEY, function (CacheItemInterface $item) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 minutes'));

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
                $item->expiresAfter(\DateInterval::createFromDateString('30 minutes'));

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
                'subService' => [$subService],
                'status' => [SupportGroup::STATUS_IN_PROGRESS],
            ];

            $nbActiveSupportsGroups = $this->supportGroupRepo->count($criteria);

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
                'device' => [$device],
                'status' => [SupportGroup::STATUS_IN_PROGRESS],
            ];

            $nbActiveSupportsGroups = $this->supportGroupRepo->count($criteria);

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
            $item->expiresAfter(\DateInterval::createFromDateString('30 minutes'));

            $users = [];

            foreach ($this->UserRepo->findUsers(['status' => 1]) as $user) {
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
            ->setNbCreatedPeople($this->personRepo->countPeople($criteriaByCreation))
            ->setNbCreatedGroups($this->peopleGroupRepo->countGroups($criteriaByCreation))
            ->setNbCreatedSupportsGroup($this->supportGroupRepo->countSupports($criteriaByCreation))
            ->setNbUpdatedSupportsGroup($this->supportGroupRepo->countSupports($criteriaByUpdate))
            ->setNbCreatedSupportsPeople($this->supportPersonRepo->countSupportPeople($criteriaByCreation))
            ->setNbCreatedEvaluations($this->evaluationRepo->countEvaluations($criteriaByCreation))
            ->setNbCreatedNotes($this->noteRepo->countNotes($criteriaByCreation))
            ->setNbUpdatedNotes($this->noteRepo->countNotes($criteriaByUpdate))
            ->setNbCreatedRdvs($this->rdvRepo->countRdvs($criteriaByCreation))
            ->setNbCreatedDocuments($this->documentRepo->countDocuments($criteriaByCreation))
            ->setNbCreatedContributions($this->contributionRepo->countContributions($criteriaByCreation))
            ->setNbConnections($this->ConnectionRepo->countConnections($criteriaByCreation))
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
                'all' => $this->personRepo->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedPeople(),
                'today' => $this->personRepo->countPeople($criteriaByCreation),
            ],
            'Nb. de groupes créés' => [
                'all' => $this->peopleGroupRepo->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedGroups(),
                'today' => $this->peopleGroupRepo->countGroups($criteriaByCreation),
            ],
            'Nb. de suivis créés' => [
                'all' => $allSupports = $this->supportGroupRepo->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedSupportsGroup(),
                'today' => $this->supportGroupRepo->countSupports($criteriaByCreation),
            ],
            'Nb. de suivis mis à jour' => [
                'all' => $allSupports,
                'yesterday' => $yesterdayIndicator->getNbUpdatedSupportsGroup(),
                'today' => $this->supportGroupRepo->countSupports($criteriaByUpdate),
            ],
            'Nb. de suivis en cours' => [
                'all' => $this->supportGroupRepo->count(['status' => SupportGroup::STATUS_IN_PROGRESS]),
                'yesterday' => '',
                'today' => '',
            ],
            'Nb. de notes créées' => [
                'all' => $this->noteRepo->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedNotes(),
                'today' => $this->noteRepo->countNotes($criteriaByCreation),
            ],
            'Nb. de notes mises à jour' => [
                'all' => '',
                'yesterday' => $yesterdayIndicator->getNbUpdatedNotes(),
                'today' => $this->noteRepo->countNotes($criteriaByUpdate),
            ],
            'Nb. de RDVs créés' => [
                'all' => $this->rdvRepo->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedRdvs(),
                'today' => $this->rdvRepo->countRdvs($criteriaByCreation),
            ],
            'Nb. de documents créés' => [
                'all' => $this->documentRepo->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedDocuments(),
                'today' => $this->documentRepo->countDocuments($criteriaByCreation),
            ],
            'Nb. de paiements créés' => [
                'all' => $this->contributionRepo->count([]),
                'yesterday' => $yesterdayIndicator->getNbCreatedContributions(),
                'today' => $this->contributionRepo->countContributions($criteriaByCreation),
            ],
            'Nb. de connexions' => [
                'all' => $this->ConnectionRepo->count([]),
                'yesterday' => $yesterdayIndicator->getNbConnections(),
                'today' => $this->ConnectionRepo->countConnections($criteriaByCreation),
            ],
        ];
    }

    /**
     * Donne les indicateurs d'un service.
     */
    protected function getServiceDatas(Service $service): array
    {
        $criteria = [
            'service' => [$service],
            'status' => [SupportGroup::STATUS_IN_PROGRESS],
        ];

        $nbActiveSupportsGroups = $this->supportGroupRepo->count($criteria);

        return [
            'name' => $service->getName(),
            'nbActiveSupportsGroups' => $nbActiveSupportsGroups,
            'nbActiveSupportsPeople' => $this->supportPersonRepo->countSupportPeople($criteria),
            'avgTimeSupport' => $this->supportGroupRepo->avgTimeSupport($criteria),
            'siaoRequest' => $this->supportGroupRepo->countSupports([
                'service' => [$service],
                'status' => [SupportGroup::STATUS_IN_PROGRESS],
                'siaoRequest' => Choices::YES,
            ]),
            'socialHousingRequest' => $this->supportGroupRepo->countSupports([
                'service' => [$service],
                'status' => [SupportGroup::STATUS_IN_PROGRESS],
                'socialHousingRequest' => Choices::YES,
            ]),
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
            'nbActiveSupportsGroups' => $nbActiveSupportsGroups,
            'nbActiveSupportsPeople' => $this->supportPersonRepo->countSupportPeople($criteria),
            'avgTimeSupport' => $this->supportGroupRepo->avgTimeSupport($criteria),
            'siaoRequest' => $this->supportGroupRepo->countSupports([
                'subService' => $subService,
                'status' => [SupportGroup::STATUS_IN_PROGRESS],
                'siaoRequest' => Choices::YES,
            ]),
            'socialHousingRequest' => $this->supportGroupRepo->countSupports([
                'subService' => $subService,
                'status' => [SupportGroup::STATUS_IN_PROGRESS],
                'socialHousingRequest' => Choices::YES,
            ]),
        ];
    }

    /**
     * Donne les indicateurs d'un dispositif.
     */
    protected function getDeviceDatas(Device $device, array $criteria, int $nbActiveSupportsGroups): array
    {
        return [
            'name' => $device->getName(),
            'nbActiveSupportsGroups' => $nbActiveSupportsGroups,
            'nbActiveSupportsPeople' => $this->supportPersonRepo->countSupportPeople($criteria),
            'avgTimeSupport' => $this->supportGroupRepo->avgTimeSupport($criteria),
            'siaoRequest' => $this->supportGroupRepo->countSupports([
                'device' => [$device],
                'status' => [SupportGroup::STATUS_IN_PROGRESS],
                'siaoRequest' => Choices::YES,
            ]),
            'socialHousingRequest' => $this->supportGroupRepo->countSupports([
                'device' => [$device],
                'status' => [SupportGroup::STATUS_IN_PROGRESS],
                'socialHousingRequest' => Choices::YES,
            ]),
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
            'nbActiveSupports' => $this->supportGroupRepo->count([
                'referent' => $user,
                'status' => SupportGroup::STATUS_IN_PROGRESS,
            ]),
            'nbNotes' => $this->noteRepo->count(['createdBy' => $user]),
            'nbRvs' => $this->rdvRepo->count(['createdBy' => $user]),
            'nbDocuments' => $this->documentRepo->count(['createdBy' => $user]),
            'nbContributions' => $this->contributionRepo->count(['createdBy' => $user]),
        ];
    }

    /**
     * Donne les services de l'utilisateur en cache.
     */
    public function getUserServices(User $user): ?array
    {
        return $this->cache->get(User::CACHE_USER_SERVICES_KEY.$user->getId(), function (CacheItemInterface $item) use ($user) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $this->serviceRepo->findServicesAndSubServicesOfUser($user);
        });
    }

    /**
     * Donne les suivis de l'utilisateur en cache.
     */
    public function getUserSupports(User $user): ?array
    {
        return $this->cache->get(User::CACHE_USER_SUPPORTS_KEY.$user->getId(), function (CacheItemInterface $item) use ($user) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            return $this->supportGroupRepo->findSupportsOfUser($user);
        });
    }

    /**
     * Donne les notes de l'utilisateur en cache.
     */
    public function getUserNotes(User $user): ?array
    {
        return $this->cache->get(User::CACHE_USER_NOTES_KEY.$user->getId(), function (CacheItemInterface $item) use ($user) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            return $this->noteRepo->findNotesOfUser($user, 10);
        });
    }

    /**
     * Donne les rdvs de l'utilisateur en cache.
     */
    public function getUserRdvs(User $user): ?array
    {
        return $this->cache->get(User::CACHE_USER_RDVS_KEY.$user->getId(), function (CacheItemInterface $item) use ($user) {
            $item->expiresAfter(\DateInterval::createFromDateString('24 hours'));

            return $this->rdvRepo->findRdvsOfUser($user, 10);
        });
    }
}
