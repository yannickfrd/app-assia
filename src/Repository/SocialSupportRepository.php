<?php

namespace App\Repository;

use App\Entity\SocialSupport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SocialSupport|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialSupport|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialSupport[]    findAll()
 * @method SocialSupport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialSupportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialSupport::class);
    }

    // /**
    //  * @return SocialSupport[] Returns an array of SocialSupport objects
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
    public function findOneBySomeField($value): ?SocialSupport
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
