<?php

namespace App\Repository;

use App\Entity\SocialSupportGrp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SocialSupportGrp|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialSupportGrp|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialSupportGrp[]    findAll()
 * @method SocialSupportGrp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialSupportGrpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialSupportGrp::class);
    }

    // /**
    //  * @return SocialSupportGrp[] Returns an array of SocialSupportGrp objects
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
    public function findOneBySomeField($value): ?SocialSupportGrp
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
