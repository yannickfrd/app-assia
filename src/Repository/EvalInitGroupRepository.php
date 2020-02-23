<?php

namespace App\Repository;

use App\Entity\InitEvalGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method InitEvalGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method InitEvalGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method InitEvalGroup[]    findAll()
 * @method InitEvalGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InitEvalGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InitEvalGroup::class);
    }

    // /**
    //  * @return InitEvalGroup[] Returns an array of InitEvalGroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?InitEvalGroup
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
