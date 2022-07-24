<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Form\Model\Admin\OccupancySearch;
use App\Form\Model\Organization\DeviceSearch;
use App\Form\Utils\Choices;
use App\Repository\Traits\QueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Device|null find($id, $lockMode = null, $lockVersion = null)
 * @method Device|null findOneBy(array $criteria, array $orderBy = null)
 * @method Device[]    findAll()
 * @method Device[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeviceRepository extends ServiceEntityRepository
{
    use QueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    /**
     * Retourne tous les dispositifs.
     */
    public function findDevicesQuery(DeviceSearch $search, $user): Query
    {
        $qb = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd')
            ->leftJoin('sd.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id, name}');

        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('sd.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        if ($search->getName()) {
            $qb->andWhere('d.name LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getService()) {
            $qb->andWhere('sd.service = :service')
                ->setParameter('service', $search->getService());
        }
        if ($search->getPole()) {
            $qb->andWhere('s.pole = :pole')
                ->setParameter('pole', $search->getPole());
        }
        if (Choices::DISABLED === $search->getDisabled()) {
            $qb->andWhere('d.disabledAt IS NOT NULL');
        } elseif (Choices::ACTIVE === $search->getDisabled()) {
            $qb->andWhere('d.disabledAt IS NULL');
        }

        return $qb
            ->orderBy('d.name', 'ASC')
            ->getQuery();
    }

    /**
     * Donne les dispositifs du service.
     *
     * @return Device[]
     */
    public function getDevicesOfService(Service $service): array
    {
        return $this->createQueryBuilder('d')->select('PARTIAL d.{id, name}')
            ->leftJoin('d.serviceDevices', 'sd')

            ->where('d.disabledAt IS NULL')
            ->andWhere('sd.service = :service')
            ->setParameter('service', $service)

            ->orderBy('d.name', 'ASC')

            ->getQuery()
            ->getResult();
    }

    /**
     * Donne la liste des dispositifs du service.
     */
    public function getDevicesOfServiceQueryBuilder(Service $service): QueryBuilder
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
    public function getDevicesOfUserQueryBuilder(User $user, Service $service = null, string $dataClass = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd')
            ->leftJoin('sd.service', 's')->addSelect('PARTIAL s.{id, name, type}');

        if ($dataClass) {
            $qb = $this->filterByServiceType($qb, $dataClass);
        }

        if ($service) {
            $qb->andWhere('sd.service = :service')
                ->setParameter('service', $service);
        }

        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('sd.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        return $qb
            ->andWhere('d.disabledAt IS NULL')
            ->orderBy('d.name', 'ASC');
    }

    /**
     * @return Device[]
     */
    public function findDevicesWithPlace(OccupancySearch $search, User $user, Service $service = null): array
    {
        $qb = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.places', 'pl')->addSelect('PARTIAL pl.{id, name, startDate, endDate, nbPlaces, service}')

            ->where('d.disabledAt IS NULL')
            ->andWhere('pl.endDate > :start OR pl.endDate IS NULL')->setParameter('start', $search->getStart())
            ->andWhere('pl.startDate < :end')->setParameter('end', $search->getEnd());

        // if ($search->getPole()) {
        //     $qb->andWhere('s.pole = :pole')
        //         ->setParameter('pole', $search->getPole());
        // }

        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('pl.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }
        if ($service) {
            $qb->andWhere('pl.service = :service')
                ->setParameter('service', $service);
        }

        return $qb
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Device[]
     */
    public function findDevicesForDashboard(User $user): array
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd')
            ->leftJoin('sd.service', 's')->addSelect('PARTIAL s.{id, name, coefficient}')

            ->where('d.disabledAt IS NULL');

        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('sd.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        return $qb
            ->orderBy('d.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
