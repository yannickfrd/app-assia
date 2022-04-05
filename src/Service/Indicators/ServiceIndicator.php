<?php

namespace App\Service\Indicators;

use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Form\Model\Admin\ServiceIndicatorsSearch;
use App\Repository\Admin\IndicatorRepository;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Event\RdvRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\UserConnectionRepository;
use App\Repository\Organization\UserRepository;
use App\Repository\People\PeopleGroupRepository;
use App\Repository\Support\DocumentRepository;
use App\Repository\Support\NoteRepository;
use App\Repository\Support\PaymentRepository;
use App\Repository\Support\SupportGroupRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ServiceIndicator
{
    protected $indicatorRepo;
    protected $userRepo;
    protected $serviceRepo;
    protected $peopleGroupRepo;
    protected $supportGroupRepo;
    protected $evaluationRepo;
    protected $noteRepo;
    protected $rdvRepo;
    protected $documentRepo;
    protected $paymentRepo;
    protected $ConnectionRepo;

    /** @var array */
    protected $criteria = [];

    protected $cache;

    public function __construct(
        IndicatorRepository $indicatorRepo,
        UserRepository $userRepo,
        ServiceRepository $serviceRepo,
        PeopleGroupRepository $peopleGroupRepo,
        SupportGroupRepository $supportGroupRepo,
        EvaluationGroupRepository $evaluationRepo,
        NoteRepository $noteRepo,
        RdvRepository $rdvRepo,
        DocumentRepository $documentRepo,
        PaymentRepository $paymentRepo,
        UserConnectionRepository $ConnectionRepo
    ) {
        $this->indicatorRepo = $indicatorRepo;
        $this->userRepo = $userRepo;
        $this->serviceRepo = $serviceRepo;
        $this->peopleGroupRepo = $peopleGroupRepo;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->evaluationRepo = $evaluationRepo;
        $this->noteRepo = $noteRepo;
        $this->rdvRepo = $rdvRepo;
        $this->documentRepo = $documentRepo;
        $this->paymentRepo = $paymentRepo;
        $this->ConnectionRepo = $ConnectionRepo;

        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Donne les indicateurs des services.
     */
    public function getServicesIndicators(ServiceIndicatorsSearch $search): array
    {
        $this->criteria = $this->getCriteria($search);

        $datasServices = [];

        foreach ($this->serviceRepo->findServices($search) as $service) {
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

            $nbSupports = $this->supportGroupRepo->count($criteria);

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
            'nbSocialWorkers' => $this->userRepo->countUsers([
                'service' => [$service],
                'status' => [1],
            ]),
            'nbSupports' => $this->supportGroupRepo->countSupports($this->criteria),
            'nbNotes' => $this->noteRepo->countNotes($this->criteria),
            'nbRdvs' => $this->rdvRepo->countRdvs($this->criteria),
            'nbDocuments' => $this->documentRepo->countDocuments($this->criteria),
            'nbPayments' => $this->paymentRepo->countPayments($this->criteria),
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
            'nbNotes' => $this->noteRepo->countNotes($criteria),
            'nbRdvs' => $this->rdvRepo->countRdvs($criteria),
            'nbDocuments' => $this->documentRepo->countDocuments($criteria),
            'nbPayments' => $this->paymentRepo->countPayments($criteria),
        ];
    }
}
