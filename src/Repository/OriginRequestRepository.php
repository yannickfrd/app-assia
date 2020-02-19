<?php

namespace App\Repository;

use App\Entity\OriginRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method OriginRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method OriginRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method OriginRequest[]    findAll()
 * @method OriginRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OriginRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OriginRequest::class);
    }

    // /**
    //  * @return OriginRequest[] Returns an array of OriginRequest objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OriginRequest
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
