<?php

namespace App\Service\Indicators;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Form\Model\Admin\OccupancySearch;
use App\Repository\Organization\AccommodationRepository;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\Support\AccommodationPersonRepository;
use App\Security\CurrentUserService;

class OccupancyIndicators
{
    protected $currentUser;
    protected $repoAccommodation;
    protected $repoAccommodatioPerson;
    protected $repoService;
    protected $repoSubService;
    protected $repoDevice;

    protected $datas = [];
    protected $nbPlaces = 0;
    protected $nbAccommodations = 0;
    protected $capacityDays = 0;
    protected $nbAccommodationsPeople = 0;
    protected $occupancyDays = 0;

    public function __construct(
        CurrentUserService $currentUser,
        AccommodationRepository $repoAccommodation,
        AccommodationPersonRepository $repoAccommodatioPerson,
        ServiceRepository $repoService,
        SubServiceRepository $repoSubService,
        DeviceRepository $repoDevice)
    {
        $this->currentUser = $currentUser;
        $this->repoAccommodation = $repoAccommodation;
        $this->repoAccommodatioPerson = $repoAccommodatioPerson;
        $this->repoService = $repoService;
        $this->repoSubService = $repoSubService;
        $this->repoDevice = $repoDevice;
    }

    /**
     * Donne tous les dispositifs avec leur taux d'occupation.
     */
    public function getOccupancyRateByDevice(OccupancySearch $search, Service $service = null): array
    {
        $devices = $this->repoDevice->findDevicesWithAccommodation($search, $this->currentUser, $service);
        $accommodationPeople = $this->repoAccommodatioPerson->findAccommodationPeople($search, $this->currentUser, $service);
        $interval = date_diff($search->getStart(), $search->getEnd());

        foreach ($devices as $device) {
            $nbPlaces = 0;
            $nbAccommodations = 0;
            $capacityDays = 0;
            $nbAccommodationsPeople = 0;
            $occupancyDays = 0;

            foreach ($device->getAccommodations() as $accommodation) {
                $dateInterval = $this->getDateInterval($accommodation->getStartDate(), $accommodation->getEndDate(), $search->getStart(), $search->getEnd());
                $capacityDays += $dateInterval->format('%a') * $accommodation->getNbPlaces();
                $nbPlaces += $accommodation->getNbPlaces();
                ++$nbAccommodations;
            }

            foreach ($accommodationPeople as $accommodationPerson) {
                if ($accommodationPerson->getAccommodationGroup()->getAccommodation()->getDevice() == $device) {
                    $dateInterval = $this->getDateInterval($accommodationPerson->getStartDate(), $accommodationPerson->getEndDate(), $search->getStart(), $search->getEnd());
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbAccommodationsPeople;
                }
            }

            $this->datas[$device->getId()] = [
                'name' => $device->getName(),
                'nbAccommodations' => $nbAccommodations,
                'nbPlaces' => $nbPlaces,
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbAccommodations ? ($capacityDays / $interval->format('%a')) : null,
                'averageOccupancy' => $nbAccommodationsPeople ? ($occupancyDays / $interval->format('%a')) : null,
            ];

            $this->nbAccommodations += $nbAccommodations;
            $this->nbPlaces += $nbPlaces;
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbAccommodationsPeople += $nbAccommodationsPeople;
        }

        return [
            'devices' => $this->datas,
            'interval' => $interval->format('%a'),
            'nbAccommodations' => $this->nbAccommodations,
            'nbPlaces' => $this->nbPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $this->nbAccommodations ? ($this->capacityDays / $interval->format('%a')) : null,
            'averageOccupancy' => $this->nbAccommodationsPeople ? ($this->occupancyDays / $interval->format('%a')) : null,
        ];
    }

    /**
     * Donne tous les services avec leur taux d'occupation.
     */
    public function getOccupancyRateByService(OccupancySearch $search, Device $device = null): array
    {
        $services = $this->repoService->findServicesWithAccommodation($search, $this->currentUser, $device);
        $accommodationPeople = $this->repoAccommodatioPerson->findAccommodationPeople($search, $this->currentUser);
        $interval = date_diff($search->getStart(), $search->getEnd());

        foreach ($services as $service) {
            $nbPlaces = 0;
            $nbAccommodations = 0;
            $capacityDays = 0;
            $nbAccommodationsPeople = 0;
            $occupancyDays = 0;

            foreach ($service->getAccommodations() as $accommodation) {
                $dateInterval = $this->getDateInterval($accommodation->getStartDate(), $accommodation->getEndDate(), $search->getStart(), $search->getEnd());
                $capacityDays += $dateInterval->format('%a') * $accommodation->getNbPlaces();
                $nbPlaces += $accommodation->getNbPlaces();
                ++$nbAccommodations;
            }

            foreach ($accommodationPeople as $accommodationPerson) {
                if ($accommodationPerson->getAccommodationGroup()->getAccommodation()->getService() == $service) {
                    $dateInterval = $this->getDateInterval($accommodationPerson->getStartDate(), $accommodationPerson->getEndDate(), $search->getStart(), $search->getEnd());
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbAccommodationsPeople;
                }
            }

            $this->datas[$service->getId()] = [
                'name' => $service->getName(),
                'nbSubServices' => $service->getSubServices()->count(),
                'nbAccommodations' => $nbAccommodations,
                'nbPlaces' => $nbPlaces,
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbAccommodations && $interval->format('%a') ? ($capacityDays / $interval->format('%a')) : null,
                'averageOccupancy' => $nbAccommodationsPeople && $interval->format('%a') ? ($occupancyDays / $interval->format('%a')) : null,
            ];

            $this->nbAccommodations += $nbAccommodations;
            $this->nbPlaces += $nbPlaces;
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbAccommodationsPeople += $nbAccommodationsPeople;
        }

        return [
            'services' => $this->datas,
            'interval' => $interval->format('%a'),
            'nbAccommodations' => $this->nbAccommodations,
            'nbPlaces' => $this->nbPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $this->nbAccommodations && $interval->format('%a') ? ($this->capacityDays / $interval->format('%a')) : null,
            'averageOccupancy' => $this->nbAccommodationsPeople && $interval->format('%a') ? ($this->occupancyDays / $interval->format('%a')) : null,
        ];
    }

    /**
     * Donne tous les sous-services du service avec leur taux d'occupation.
     */
    public function getOccupancyRateBySubService(OccupancySearch $search, Service $service): array
    {
        $subServices = $this->repoSubService->findSubServicesWithAccommodation($search, $this->currentUser, $service);
        $accommodationPeople = $this->repoAccommodatioPerson->findAccommodationPeople($search, $this->currentUser);
        $interval = date_diff($search->getStart(), $search->getEnd());

        foreach ($subServices as $subService) {
            $nbPlaces = 0;
            $nbAccommodations = 0;
            $capacityDays = 0;
            $nbAccommodationsPeople = 0;
            $occupancyDays = 0;

            foreach ($subService->getAccommodations() as $accommodation) {
                $dateInterval = $this->getDateInterval($accommodation->getStartDate(), $accommodation->getEndDate(), $search->getStart(), $search->getEnd());
                $capacityDays += $dateInterval->format('%a') * $accommodation->getNbPlaces();
                $nbPlaces += $accommodation->getNbPlaces();
                ++$nbAccommodations;
            }

            foreach ($accommodationPeople as $accommodationPerson) {
                if ($accommodationPerson->getAccommodationGroup()->getAccommodation()->getSubService() == $subService) {
                    $dateInterval = $this->getDateInterval($accommodationPerson->getStartDate(), $accommodationPerson->getEndDate(), $search->getStart(), $search->getEnd());
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbAccommodationsPeople;
                }
            }

            $this->datas[$subService->getId()] = [
                'name' => $subService->getName(),
                'nbAccommodations' => $nbAccommodations,
                'nbPlaces' => $nbPlaces,
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbAccommodations ? ($capacityDays / $interval->format('%a')) : null,
                'averageOccupancy' => $nbAccommodationsPeople ? ($occupancyDays / $interval->format('%a')) : null,
            ];

            $this->nbAccommodations += $nbAccommodations;
            $this->nbPlaces += $nbPlaces;
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbAccommodationsPeople += $nbAccommodationsPeople;
        }

        return [
            'subServices' => $this->datas,
            'interval' => $interval->format('%a'),
            'nbAccommodations' => $this->nbAccommodations,
            'nbPlaces' => $this->nbPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $this->nbAccommodations ? ($this->capacityDays / $interval->format('%a')) : null,
            'averageOccupancy' => $this->nbAccommodationsPeople ? ($this->occupancyDays / $interval->format('%a')) : null,
        ];
    }

    /**
     * Donne tous les groupes de places d'un service avec leur taux d'occupation.
     */
    public function getOccupancyRateByAccommodation(OccupancySearch $search, Service $service = null, SubService $subService = null): array
    {
        $accommodations = $this->repoAccommodation->findAccommodationsForOccupancy($search, $this->currentUser, $service, $subService);
        $accommodationPeople = $this->repoAccommodatioPerson->findAccommodationPeople($search, $this->currentUser, $service, $subService);
        $interval = date_diff($search->getStart(), $search->getEnd());

        foreach ($accommodations as $accommodation) {
            $capacityDays = 0;
            $nbAccommodationsPeople = 0;
            $occupancyDays = 0;

            $dateInterval = $this->getDateInterval($accommodation->getStartDate(), $accommodation->getEndDate(), $search->getStart(), $search->getEnd());
            $capacityDays += $dateInterval->format('%a') * $accommodation->getNbPlaces();

            foreach ($accommodationPeople as $accommodationPerson) {
                if ($accommodationPerson->getAccommodationGroup()->getAccommodation() == $accommodation) {
                    $dateInterval = $this->getDateInterval($accommodationPerson->getStartDate(), $accommodationPerson->getEndDate(), $search->getStart(), $search->getEnd());
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbAccommodationsPeople;
                }
            }

            $this->datas[$accommodation->getId()] = [
                'accommodation' => $accommodation,
                'nbPlaces' => $accommodation->getNbPlaces(),
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $capacityDays / $interval->format('%a'),
                'averageOccupancy' => $nbAccommodationsPeople ? ($occupancyDays / $interval->format('%a')) : null,
            ];

            ++$this->nbAccommodations;
            $this->nbPlaces += $accommodation->getNbPlaces();
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbAccommodationsPeople += $nbAccommodationsPeople;
        }

        return [
            'accommodations' => $this->datas,
            'interval' => $interval->format('%a'),
            'nbAccommodations' => $this->nbAccommodations,
            'nbPlaces' => $this->nbPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $this->nbAccommodations ? ($this->capacityDays / $interval->format('%a')) : null,
            'averageOccupancy' => $this->nbAccommodationsPeople ? ($this->occupancyDays / $interval->format('%a')) : null,
        ];
    }

    /**
     * Donne l'intervalle entre 2 dates.
     */
    protected function getDateInterval(\DateTimeInterface $startDate1, \DateTimeInterface $endDate1 = null, \DateTime $startDate2, \DateTime $endDate2): ?\DateInterval
    {
        $minDate = max($startDate1, $startDate2);
        $maxDate = min($endDate1 ?? $endDate2, $endDate2);

        return date_diff($maxDate, $minDate);
    }
}
