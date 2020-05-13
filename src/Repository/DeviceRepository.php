<?php

namespace App\Repository;

use App\Entity\Device;
use Doctrine\ORM\Query;
use App\Entity\Accommodation;
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
    public function getDevicesFromUserQueryList(CurrentUserService $currentUser)
    {
        $query = $this->createQueryBuilder('d')->select('PARTIAL d.{id, name, coefficient}')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd');

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->where('sd.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query->orderBy('d.name', 'ASC');
    }

    public function findDevicesWithAccommodation(CurrentUserService $currentUser, \DateTime $start, \DateTime $end)
    {
        $query = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.accommodations', 'a')->addSelect('PARTIAL a.{id, name, startDate, endDate, nbPlaces}')

            ->andWhere('a.endDate > :start OR a.endDate IS NULL')->setParameter('start', $start)
            ->andWhere('a.startDate < :end')->setParameter('end', $end);

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('d.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query
            ->orderBy('d.name', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function findDevicesForDashboard(CurrentUserService $currentUser)
    {
        return ($this->getDevicesFromUserQueryList($currentUser))
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
