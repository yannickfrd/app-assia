<?php

namespace App\Service\Indicators;

use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Form\Model\Admin\ServiceIndicatorsSearch;
use App\Repository\Admin\IndicatorRepository;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\UserConnectionRepository;
use App\Repository\Organization\UserRepository;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\Support\ContributionRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\RdvRepository;
use App\Repository\Support\SupportGroupRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Security\Core\Security;

class ServiceIndicator
{
    /**
     * @var User
     */
    protected $user;

    protected $repoIndicator;
    protected $repoUser;
    protected $repoService;
    protected $repoPeopleGroup;
    /**
     * @var SupportGroupRepository
     */
    protected $repoSupportGroup;
    protected $repoEvaluation;
    protected $repoNote;
    protected $repoRdv;
    protected $repoDocument;
    protected $repoContribution;
    protected $repoConnection;

    /**
     * @var array
     */
    protected $criteria;

    protected $cache;

    public function __construct(
        Security $security,
        IndicatorRepository $repoIndicator,
        UserRepository $repoUser,
        ServiceRepository $repoService,
        PeopleGroupRepository $repoPeopleGroup,
        SupportGroupRepository $repoSupportGroup,
        EvaluationGroupRepository $repoEvaluation,
        NoteRepository $repoNote,
        RdvRepository $repoRdv,
        DocumentRepository $repoDocument,
        ContributionRepository $repoContribution,
        UserConnectionRepository $repoConnection)
    {
        $this->user = $security->getUser();

        $this->repoIndicator = $repoIndicator;
        $this->repoUser = $repoUser;
        $this->repoService = $repoService;
        $this->repoPeopleGroup = $repoPeopleGroup;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoEvaluation = $repoEvaluation;
        $this->repoNote = $repoNote;
        $this->repoRdv = $repoRdv;
        $this->repoDocument = $repoDocument;
        $this->repoContribution = $repoContribution;
        $this->repoConnection = $repoConnection;

        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Donne les indicateurs des services.
     */
    public function getServicesIndicators(ServiceIndicatorsSearch $search): array
    {
        $this->criteria = $this->getCriteria($search);

        $datasServices = [];

        foreach ($this->repoService->findServices($search) as $service) {
            $datasServices[] = $this->getServiceDatas($service);
        }

        return $datasServices;
    }

    private function getCriteria(ServiceIndicatorsSearch $search)
    {
        $criteria['filterDateBy'] = 'updatedAt';

        if ($search->getStatus() && count($search->getStatus()) > 0) {
            $criteria['status'] = $search->getStatus();
        }
        if ($search->getDevices() && count($search->getDevices()) > 0) {
            $criteria['device'] = $search->getDevices();
        }
        if ($search->getStart()) {
            $criteria['startDate'] = $search->getStart();
        }
        if ($search->getEnd()) {
            $criteria['endDate'] = $search->getEnd();
        }

        return $criteria;
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
            $criteria['subService'] = [$subService];

            $nbSupports = $this->repoSupportGroup->count($criteria);

            if ((int) $nbSupports > 0) {
                $datasSubServices[$subService->getId()] = $this->getSubServiceDatas($subService, $criteria, $nbSupports);
            }
        }

        return $datasSubServices;
    }

    /**
     * Donne les indicateurs d'un service.
     */
    protected function getServiceDatas(Service $service): array
    {
        $this->criteria['service'] = [$service];

        return [
            'name' => $service->getName(),
            'nbSocialWorkers' => $this->repoUser->countUsers([
                'service' => [$service],
                'status' => [1],
            ]),
            'nbSupports' => $this->repoSupportGroup->countSupports($this->criteria),
            'nbNotes' => $this->repoNote->countNotes($this->criteria),
            'nbRdvs' => $this->repoRdv->countRdvs($this->criteria),
            'nbDocuments' => $this->repoDocument->countDocuments($this->criteria),
            'nbContributions' => $this->repoContribution->countContributions($this->criteria),
            'subServices' => $this->getSubServicesIndicators($service),
        ];
    }

    /**
     * Donne les indicateurs d'un sous-service.
     */
    protected function getSubServiceDatas(SubService $subService, array $criteria, int $nbSupports): array
    {
        return [
            'name' => $subService->getName(),
            'nbSocialWorkers' => '',
            'nbSupports' => $nbSupports,
            'nbNotes' => $this->repoNote->countNotes($criteria),
            'nbRdvs' => $this->repoRdv->countRdvs($criteria),
            'nbDocuments' => $this->repoDocument->countDocuments($criteria),
            'nbContributions' => $this->repoContribution->countContributions($criteria),
        ];
    }
}
