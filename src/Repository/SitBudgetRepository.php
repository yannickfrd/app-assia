<?php

namespace App\Repository;

use App\Entity\SitBudget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SitBudget|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitBudget|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitBudget[]    findAll()
 * @method SitBudget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitBudgetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitBudget::class);
    }

    // /**
    //  * @return SitBudget[] Returns an array of SitBudget objects
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
    public function findOneBySomeField($value): ?SitBudget
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
