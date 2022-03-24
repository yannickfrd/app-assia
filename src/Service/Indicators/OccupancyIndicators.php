<?php

namespace App\Service\Indicators;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Form\Model\Admin\OccupancySearch;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\Support\PlacePersonRepository;
use App\Security\CurrentUserService;

class OccupancyIndicators
{
    protected $currentUser;
    protected $placeRepo;
    protected $placePersonRepo;
    protected $serviceRepo;
    protected $subServiceRepo;
    protected $deviceRepo;

    protected $datas = [];
    protected $sumPlaces = 0;
    protected $nbPlaces = 0;
    protected $capacityDays = 0;
    protected $nbPlacesPeople = 0;
    protected $occupancyDays = 0;

    public function __construct(
        CurrentUserService $currentUser,
        PlaceRepository $placeRepo,
        PlacePersonRepository $placePersonRepo,
        ServiceRepository $serviceRepo,
        SubServiceRepository $subServiceRepo,
        DeviceRepository $deviceRepo
    ) {
        $this->currentUser = $currentUser;
        $this->placeRepo = $placeRepo;
        $this->placePersonRepo = $placePersonRepo;
        $this->serviceRepo = $serviceRepo;
        $this->subServiceRepo = $subServiceRepo;
        $this->deviceRepo = $deviceRepo;
    }

    /**
     * Donne tous les dispositifs avec leur taux d'occupation.
     */
    public function getOccupancyRateByDevice(OccupancySearch $search, Service $service = null): array
    {
        $devices = $this->deviceRepo->findDevicesWithPlace($search, $this->currentUser, $service);
        $placePeople = $this->placePersonRepo->findPlacePeople($search, $this->currentUser, $service);
        $interval = date_diff($search->getStart(), $search->getEnd());
        $nbDays = (int) $interval->format('%a');

        foreach ($devices as $device) {
            $nbPlaces = 0;
            $sumPlaces = 0;
            $capacityDays = 0;
            $nbPlacesPeople = 0;
            $occupancyDays = 0;

            foreach ($device->getPlaces() as $place) {
                $dateInterval = $this->getDateInterval($place->getStartDate(), $place->getEndDate(), $search->getStart(), $search->getEnd());
                $capacityDays += $dateInterval->format('%a') * $place->getNbPlaces();
                $sumPlaces += $place->getNbPlaces();
                ++$nbPlaces;
            }

            foreach ($placePeople as $placePerson) {
                if ($placePerson->getPlaceGroup()->getPlace()->getDevice() == $device) {
                    $dateInterval = $this->getDateInterval($placePerson->getStartDate(), $placePerson->getEndDate(), $search->getStart(), $search->getEnd());
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbPlacesPeople;
                }
            }

            $this->datas[$device->getId()] = [
                'name' => $device->getName(),
                'nbPlaces' => $nbPlaces,
                'sumPlaces' => $sumPlaces,
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbDays && $nbPlaces ? ($capacityDays / $nbDays) : null,
                'averageOccupancy' => $nbDays && $nbPlacesPeople ? ($occupancyDays / $nbDays) : null,
            ];

            $this->nbPlaces += $nbPlaces;
            $this->sumPlaces += $sumPlaces;
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbPlacesPeople += $nbPlacesPeople;
        }

        return [
            'devices' => $this->datas,
            'interval' => $nbDays,
            'nbPlaces' => $this->nbPlaces,
            'sumPlaces' => $this->sumPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $nbDays && $this->nbPlaces ? ($this->capacityDays / $nbDays) : null,
            'averageOccupancy' => $nbDays && $this->nbPlacesPeople ? ($this->occupancyDays / $nbDays) : null,
        ];
    }

    /**
     * Donne tous les services avec leur taux d'occupation.
     */
    public function getOccupancyRateByService(OccupancySearch $search, Device $device = null): array
    {
        $services = $this->serviceRepo->findServicesWithPlace($search, $this->currentUser, $device);
        $placePeople = $this->placePersonRepo->findPlacePeople($search, $this->currentUser);
        $interval = date_diff($search->getStart(), $search->getEnd());
        $nbDays = (int) $interval->format('%a');

        foreach ($services as $service) {
            $nbPlaces = 0;
            $sumPlaces = 0;
            $capacityDays = 0;
            $nbPlacesPeople = 0;
            $occupancyDays = 0;

            foreach ($service->getPlaces() as $place) {
                $dateInterval = $this->getDateInterval($place->getStartDate(), $place->getEndDate(), $search->getStart(), $search->getEnd());
                $capacityDays += $dateInterval->format('%a') * $place->getNbPlaces();
                $sumPlaces += $place->getNbPlaces();
                ++$nbPlaces;
            }

            foreach ($placePeople as $placePerson) {
                if ($placePerson->getPlaceGroup()->getPlace()->getService() == $service) {
                    $dateInterval = $this->getDateInterval($placePerson->getStartDate(), $placePerson->getEndDate(), $search->getStart(), $search->getEnd());
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbPlacesPeople;
                }
            }

            $this->datas[$service->getId()] = [
                'name' => $service->getName(),
                'nbSubServices' => $service->getSubServices()->count(),
                'nbPlaces' => $nbPlaces,
                'sumPlaces' => $sumPlaces,
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbPlaces && $nbDays ? ($capacityDays / $nbDays) : null,
                'averageOccupancy' => $nbPlacesPeople && $nbDays ? ($occupancyDays / $nbDays) : null,
            ];

            $this->nbPlaces += $nbPlaces;
            $this->sumPlaces += $sumPlaces;
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbPlacesPeople += $nbPlacesPeople;
        }

        return [
            'services' => $this->datas,
            'interval' => $nbDays,
            'nbPlaces' => $this->nbPlaces,
            'sumPlaces' => $this->sumPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $this->nbPlaces && $nbDays ? ($this->capacityDays / $nbDays) : null,
            'averageOccupancy' => $this->nbPlacesPeople && $nbDays ? ($this->occupancyDays / $nbDays) : null,
        ];
    }

    /**
     * Donne tous les sous-services du service avec leur taux d'occupation.
     */
    public function getOccupancyRateBySubService(OccupancySearch $search, Service $service): array
    {
        $subServices = $this->subServiceRepo->findSubServicesWithPlace($search, $this->currentUser, $service);
        $placePeople = $this->placePersonRepo->findPlacePeople($search, $this->currentUser);
        $interval = date_diff($search->getStart(), $search->getEnd());
        $nbDays = (int) $interval->format('%a');

        foreach ($subServices as $subService) {
            $nbPlaces = 0;
            $sumPlaces = 0;
            $capacityDays = 0;
            $nbPlacesPeople = 0;
            $occupancyDays = 0;

            foreach ($subService->getPlaces() as $place) {
                $dateInterval = $this->getDateInterval($place->getStartDate(), $place->getEndDate(), $search->getStart(), $search->getEnd());
                $capacityDays += $dateInterval->format('%a') * $place->getNbPlaces();
                $sumPlaces += $place->getNbPlaces();
                ++$nbPlaces;
            }

            foreach ($placePeople as $placePerson) {
                if ($placePerson->getPlaceGroup()->getPlace()->getSubService() == $subService) {
                    $dateInterval = $this->getDateInterval($placePerson->getStartDate(), $placePerson->getEndDate(), $search->getStart(), $search->getEnd());
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbPlacesPeople;
                }
            }

            $this->datas[$subService->getId()] = [
                'name' => $subService->getName(),
                'nbPlaces' => $nbPlaces,
                'sumPlaces' => $sumPlaces,
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbPlaces ? ($capacityDays / $nbDays) : null,
                'averageOccupancy' => $nbPlacesPeople ? ($occupancyDays / $nbDays) : null,
            ];

            $this->nbPlaces += $nbPlaces;
            $this->sumPlaces += $sumPlaces;
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbPlacesPeople += $nbPlacesPeople;
        }

        return [
            'subServices' => $this->datas,
            'interval' => $nbDays,
            'nbPlaces' => $this->nbPlaces,
            'sumPlaces' => $this->sumPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $nbDays && $this->nbPlaces ? ($this->capacityDays / $nbDays) : null,
            'averageOccupancy' => $nbDays && $this->nbPlacesPeople ? ($this->occupancyDays / $nbDays) : null,
        ];
    }

    /**
     * Donne tous les groupes de places d'un service avec leur taux d'occupation.
     */
    public function getOccupancyRateByPlace(OccupancySearch $search, Service $service = null, SubService $subService = null): array
    {
        $places = $this->placeRepo->findPlacesForOccupancy($search, $this->currentUser, $service, $subService);
        $placePeople = $this->placePersonRepo->findPlacePeople($search, $this->currentUser, $service, $subService);
        $interval = date_diff($search->getStart(), $search->getEnd());
        $nbDays = (int) $interval->format('%a');

        foreach ($places as $place) {
            $capacityDays = 0;
            $nbPlacesPeople = 0;
            $occupancyDays = 0;

            $dateInterval = $this->getDateInterval($place->getStartDate(), $place->getEndDate(), $search->getStart(), $search->getEnd());
            $capacityDays += $dateInterval->format('%a') * $place->getNbPlaces();

            foreach ($placePeople as $placePerson) {
                if ($placePerson->getPlaceGroup()->getPlace() == $place) {
                    $dateInterval = $this->getDateInterval($placePerson->getStartDate(), $placePerson->getEndDate(), $search->getStart(), $search->getEnd());
                    $occupancyDays += $dateInterval->format('%a');
                    ++$nbPlacesPeople;
                }
            }

            $this->datas[$place->getId()] = [
                'place' => $place,
                'sumPlaces' => $place->getNbPlaces(),
                'capacityDays' => $capacityDays,
                'occupancyDays' => $occupancyDays,
                'averageCapacity' => $nbDays ? $capacityDays / $nbDays : null,
                'averageOccupancy' => $nbDays && $nbPlacesPeople ? ($occupancyDays / $nbDays) : null,
            ];

            ++$this->nbPlaces;
            $this->sumPlaces += $place->getNbPlaces();
            $this->capacityDays += $capacityDays;
            $this->occupancyDays += $occupancyDays;
            $this->nbPlacesPeople += $nbPlacesPeople;
        }

        return [
            'places' => $this->datas,
            'interval' => $nbDays,
            'nbPlaces' => $this->nbPlaces,
            'sumPlaces' => $this->sumPlaces,
            'capacityDays' => $this->capacityDays,
            'occupancyDays' => $this->occupancyDays,
            'averageCapacity' => $nbDays && $this->nbPlaces ? ($this->capacityDays / $nbDays) : null,
            'averageOccupancy' => $nbDays && $this->nbPlacesPeople ? ($this->occupancyDays / $nbDays) : null,
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
