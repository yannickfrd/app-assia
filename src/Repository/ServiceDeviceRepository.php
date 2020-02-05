<?php

namespace App\Repository;

use App\Entity\ServiceDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServiceDevice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceDevice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceDevice[]    findAll()
 * @method ServiceDevice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceDeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceDevice::class);
    }

    // /**
    //  * @return ServiceDevice[] Returns an array of ServiceDevice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ServiceDevice
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
