<?php

namespace App\Repository\Support;

use App\Entity\Support\HotelSupport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HotelSupport|null find($id, $lockMode = null, $lockVersion = null)
 * @method HotelSupport|null findOneBy(array $criteria, array $orderBy = null)
 * @method HotelSupport[]    findAll()
 * @method HotelSupport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HotelSupportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HotelSupport::class);
    }

    // /**
    //  * @return HotelSupport[] Returns an array of HotelSupport objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?HotelSupport
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
