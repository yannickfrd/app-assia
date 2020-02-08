<?php

namespace App\Repository;

use App\Entity\Device;
use Doctrine\ORM\Query;
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
     * Retourne tous les dispositifs
     * @return Query
     */
    public function findAllDevicesQuery(): Query
    {
        $query =  $this->createQueryBuilder("d")
            ->select("d");

        return $query->orderBy("d.name", "ASC")
            ->getQuery();
    }

    /** 
     * Donne la liste des dispositifs
     */
    public function getDevicesQueryList($place)
    {
        $query =  $this->createQueryBuilder("d")
            ->select("PARIAL d.{id, name, serviceDevices}")
            ->leftJoin("d.serviceDevices", "s")

            ->where("s.service = :service")
            ->setParameter("service", $place->getService());

        return $query->orderBy("d.name", "ASC");
    }
}
