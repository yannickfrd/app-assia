<?php

namespace App\Repository;

use App\Entity\SitBudgetGrp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SitBudgetGrp|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitBudgetGrp|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitBudgetGrp[]    findAll()
 * @method SitBudgetGrp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitBudgetGrpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitBudgetGrp::class);
    }

    // /**
    //  * @return SitBudgetGrp[] Returns an array of SitBudgetGrp objects
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
    public function findOneBySomeField($value): ?SitBudgetGrp
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
