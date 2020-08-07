<?php

namespace App\Repository;

use App\Entity\Device;
use App\Entity\Service;
use Doctrine\ORM\Query;
use App\Entity\Accommodation;
use App\Form\Model\SupportsByUserSearch;
use App\Security\CurrentUserService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
    public function findAllDevicesQuery(): Query
    {
        $query = $this->createQueryBuilder('d')
            ->select('d');

        return $query->orderBy('d.name', 'ASC')
            ->getQuery();
    }

    /**
     * Donne la liste des dispositifs du service.
     */
    public function getDevicesFromServiceQueryList(Accommodation $accommodation)
    {
        $query = $this->createQueryBuilder('d')->select('PARTIAL d.{id, name}')
            ->leftJoin('d.serviceDevices', 'sd')

            ->where('sd.service = :service')
            ->setParameter('service', $accommodation->getService());

        return $query->orderBy('d.name', 'ASC');
    }

    /**
     * Donne la liste des dispositifs de l'utilisateur.
     */
    public function getDevicesFromUserQueryList(CurrentUserService $currentUser, $serviceId = null)
    {
        $query = $this->createQueryBuilder('d')->select('PARTIAL d.{id, name, coefficient, accommodation}')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd');

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('sd.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($serviceId) {
            $query = $query->andWhere('sd.service = :service')
                ->setParameter('service', $serviceId);
        }

        return $query->orderBy('d.name', 'ASC');
    }

    public function findDevicesWithAccommodation(CurrentUserService $currentUser, \DateTime $start, \DateTime $end, Service $service = null)
    {
        $query = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.accommodations', 'a')->addSelect('PARTIAL a.{id, name, startDate, endDate, nbPlaces, service}')

            ->andWhere('a.endDate > :start OR a.endDate IS NULL')->setParameter('start', $start)
            ->andWhere('a.startDate < :end')->setParameter('end', $end);

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('a.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }
        if ($service) {
            $query = $query->andWhere('a.service = :service')
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
            ->leftJoin('sd.service', 's')->addSelect('PARTIAL s.{id, name}');

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
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
