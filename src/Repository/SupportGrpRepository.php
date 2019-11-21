<?php

namespace App\Repository;

use App\Entity\SupportGrp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SupportGrp|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportGrp|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportGrp[]    findAll()
 * @method SupportGrp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportGrpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportGrp::class);
    }

    // /**
    //  * @return SupportGrp[] Returns an array of SupportGrp objects
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
    public function findOneBySomeField($value): ?SupportGrp
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
