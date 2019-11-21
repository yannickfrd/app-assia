<?php

namespace App\Repository;

use App\Entity\SitSocial;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SitSocial|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitSocial|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitSocial[]    findAll()
 * @method SitSocial[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitSocialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitSocial::class);
    }

    // /**
    //  * @return SitSocial[] Returns an array of SitSocial objects
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
    public function findOneBySomeField($value): ?SitSocial
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
