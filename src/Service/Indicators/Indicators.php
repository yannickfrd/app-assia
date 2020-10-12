<?php

namespace App\Service\Indicators;

use App\Entity\Service;
use App\Entity\SupportGroup;
use App\Form\Utils\Choices;
use App\Repository\ContributionRepository;
use App\Repository\DocumentRepository;
use App\Repository\GroupPeopleRepository;
use App\Repository\NoteRepository;
use App\Repository\PersonRepository;
use App\Repository\RdvRepository;
use App\Repository\ServiceRepository;
use App\Repository\SupportGroupRepository;
use App\Repository\SupportPersonRepository;
use App\Repository\UserRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Core\Security;

class Indicators
{
    protected $security;

    protected $repoUser;
    protected $repoService;
    protected $repoPerson;
    protected $repoGroupPeople;
    protected $repoSupportGroup;
    protected $repoSupportPerson;
    protected $repoNote;
    protected $repoRdv;
    protected $repoDocument;
    protected $repoContribution;

    protected $cache;

    public function __construct(
        Security $security,
        UserRepository $repoUser,
        ServiceRepository $repoService,
        PersonRepository $repoPerson,
        GroupPeopleRepository $repoGroupPeople,
        SupportGroupRepository $repoSupportGroup,
        SupportPersonRepository $repoSupportPerson,
        NoteRepository $repoNote,
        RdvRepository $repoRdv,
        DocumentRepository $repoDocument,
        ContributionRepository $repoContribution)
    {
        $this->security = $security;

        $this->repoUser = $repoUser;
        $this->repoService = $repoService;
        $this->repoPerson = $repoPerson;
        $this->repoGroupPeople = $repoGroupPeople;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoSupportPerson = $repoSupportPerson;
        $this->repoNote = $repoNote;
        $this->repoRdv = $repoRdv;
        $this->repoDocument = $repoDocument;
        $this->repoContribution = $repoContribution;

        $this->cache = new FilesystemAdapter();
    }

    public function getIndicators()
    {
        $indicators = $this->cache->getItem('stats.indicators');

        $today = (new \DateTime('2020-02-25'));
        if (!$indicators->isHit()) {
            $datas = [];
            $datas['Nombre de personnes'] = $this->repoPerson->count([]);
            $datas['Nombre de groupes'] = $this->repoGroupPeople->count([]);
            $datas['Nombre de suivis'] = $this->repoSupportGroup->count([]);
            $datas['Nombre de suivis en cours'] = $this->repoSupportGroup->count(['status' => SupportGroup::STATUS_IN_PROGRESS]);
            $datas['Nombre de notes'] = $this->repoNote->count([]);
            $datas['Nombre de RDVs'] = $this->repoRdv->count([]);
            $datas['Nombre de documents'] = $this->repoDocument->count([]).' ('.round($this->repoDocument->sumSizeAllDocuments() / 1024 / 1024).' Mo.)';
            $datas['Nombre de paiements'] = $this->repoContribution->count([]);

            $indicators->set($datas);
            $indicators->expiresAfter(10 * 60); // 5mn
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
                    'notes' => $this->repoNote->countNotes($criteria),
                    'rdvs' => $this->repoRdv->countRdvs($criteria),
                    'documents' => $this->repoDocument->countDocuments($criteria),
                    'contributions' => $this->repoContribution->countContributions($criteria),
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
        if (0 == $service->getSubServices()->count()) {
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
                    'notes' => $this->repoNote->countNotes($criteria),
                    'rdvs' => $this->repoRdv->countRdvs($criteria),
                    'documents' => $this->repoDocument->countDocuments($criteria),
                    'contributions' => $this->repoContribution->countContributions($criteria),
                ];
            }
        }

        return $subServices;
    }

    public function getUsersIndicators(FilesystemAdapter $cache)
    {
        $usersIndicators = $cache->getItem('stats.users_indicators');

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
}
