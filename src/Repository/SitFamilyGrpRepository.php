<?php

namespace App\Repository;

use App\Entity\SitFamilyGrp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SitFamilyGrp|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitFamilyGrp|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitFamilyGrp[]    findAll()
 * @method SitFamilyGrp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitFamilyGrpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitFamilyGrp::class);
    }

    // /**
    //  * @return SitFamilyGrp[] Returns an array of SitFamilyGrp objects
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
    public function findOneBySomeField($value): ?SitFamilyGrp
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
