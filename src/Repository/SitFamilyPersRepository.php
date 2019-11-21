<?php

namespace App\Repository;

use App\Entity\SitFamilyPers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SitFamilyPers|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitFamilyPers|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitFamilyPers[]    findAll()
 * @method SitFamilyPers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitFamilyPersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitFamilyPers::class);
    }

    // /**
    //  * @return SitFamilyPers[] Returns an array of SitFamilyPers objects
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
    public function findOneBySomeField($value): ?SitFamilyPers
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
