<?php

namespace App\Repository;

use App\Entity\Device;
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
    public function getDevicesFromServiceQueryList($accommodation)
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
    public function getDevicesFromUserQueryList($currentUser)
    {
        $query = $this->createQueryBuilder('d')->select('PARTIAL d.{id, name}')
            ->leftJoin('d.serviceDevices', 'sd')->addSelect('sd');

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->where('sd.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query->orderBy('d.name', 'ASC');
    }
}
