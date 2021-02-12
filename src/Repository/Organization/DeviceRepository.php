<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Form\Model\Admin\OccupancySearch;
use App\Form\Model\Organization\DeviceSearch;
use App\Form\Model\Support\SupportsByUserSearch;
use App\Form\Utils\Choices;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Device|null find($id, $lockMode = null, $lockVersion = null)
 * @method Device|null findOneBy(array $criteria, array $orderBy = null)
 * @method Device[]    findAll()
 * @method Device[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    /**
     * Retourne tous les dispositifs.
     */
    public function findDevicesQuery(CurrentUserService $currentUser, DeviceSearch $search): Query
    {
        $query = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd')
            ->leftJoin('sd.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id, name}');

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('sd.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($search->getName()) {
            $query->andWhere('d.name LIKE :name')
                ->setParameter('name', $search->getName().'%');
        }
        if ($search->getService()) {
            $query->andWhere('sd.service = :service')
                ->setParameter('service', $search->getService());
        }
        if ($search->getPole()) {
            $query->andWhere('s.pole = :pole')
                ->setParameter('pole', $search->getPole());
        }
        if (Choices::DISABLED === $search->getDisabled()) {
            $query->andWhere('d.disabledAt IS NOT NULL');
        } elseif (Choices::ACTIVE === $search->getDisabled()) {
            $query->andWhere('d.disabledAt IS NULL');
        }

        return $query->orderBy('d.name', 'ASC')
            ->getQuery();
    }

    /**
     * Donne les dispositifs du service.
     *
     * @return Device[]|null
     */
    public function getDevicesOfService(int $id): ?array
    {
        return $this->createQueryBuilder('d')->select('PARTIAL d.{id, name}')
            ->leftJoin('d.serviceDevices', 'sd')

            ->where('d.disabledAt IS NULL')
            ->andWhere('sd.service = :service')
            ->setParameter('service', $id)

            ->orderBy('d.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la liste des dispositifs du service.
     */
    public function getDevicesOfServiceQueryList(Service $service)
    {
        return $this->createQueryBuilder('d')->select('PARTIAL d.{id, name}')
            ->leftJoin('d.serviceDevices', 'sd')

            ->where('d.disabledAt IS NULL')
            ->andWhere('sd.service = :service')
            ->setParameter('service', $service)

            ->orderBy('d.name', 'ASC');
    }

    /**
     * Donne la liste des dispositifs de l'utilisateur.
     */
    public function getDevicesOfUserQueryList(CurrentUserService $currentUser, $serviceId = null, Device $device = null)
    {
        $query = $this->createQueryBuilder('d')->select('PARTIAL d.{id, name, coefficient, place, disabledAt}')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd');

        if ($serviceId) {
            $query = $query->andWhere('sd.service = :service')
                ->setParameter('service', $serviceId);
        }

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('sd.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        $query = $query->andWhere('d.disabledAt IS NULL');

        if ($device) {
            $query = $query->orWhere('d.id = :device')
                ->setParameter('device', $device);
        }

        return $query->orderBy('d.name', 'ASC');
    }

    public function findDevicesWithPlace(OccupancySearch $search, CurrentUserService $currentUser, Service $service = null)
    {
        $query = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.places', 'pl')->addSelect('PARTIAL pl.{id, name, startDate, endDate, nbPlaces, service}')

            ->where('d.disabledAt IS NULL')
            ->andWhere('pl.endDate > :start OR pl.endDate IS NULL')->setParameter('start', $search->getStart())
            ->andWhere('pl.startDate < :end')->setParameter('end', $search->getEnd());

        // if ($search->getPole()) {
        //     $query = $query->andWhere('s.pole = :pole')
        //         ->setParameter('pole', $search->getPole());
        // }

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('pl.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }
        if ($service) {
            $query = $query->andWhere('pl.service = :service')
                ->setParameter('service', $service);
        }

        return $query
            ->orderBy('d.name', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function findDevicesForDashboard(CurrentUserService $currentUser, SupportsByUserSearch $search)
    {
        $query = $this->createQueryBuilder('d')->select('PARTIAL d.{id, name, coefficient}')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd')
            ->leftJoin('sd.service', 's')->addSelect('PARTIAL s.{id, name}')

            ->where('d.disabledAt IS NULL');

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->where('sd.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($search->getServices() && $search->getServices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sd.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($search->getDevices() && $search->getDevices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sd.device', $device));
            }
            $query->andWhere($orX);
        }

        return $query
            ->orderBy('d.name', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
