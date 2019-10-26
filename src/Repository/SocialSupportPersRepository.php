<?php

namespace App\Repository;

use App\Entity\SocialSupportPers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SocialSupportPers|null find($id, $lockMode = null, $lockVersion = null)
 * @method SocialSupportPers|null findOneBy(array $criteria, array $orderBy = null)
 * @method SocialSupportPers[]    findAll()
 * @method SocialSupportPers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SocialSupportPersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialSupportPers::class);
    }

    // /**
    //  * @return SocialSupportPers[] Returns an array of SocialSupportPers objects
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
    public function findOneBySomeField($value): ?SocialSupportPers
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
