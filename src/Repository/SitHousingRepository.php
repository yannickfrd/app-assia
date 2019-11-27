<?php

namespace App\Repository;

use App\Entity\SitHousing;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SitHousing|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitHousing|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitHousing[]    findAll()
 * @method SitHousing[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitHousingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitHousing::class);
    }

    // /**
    //  * @return SitHousing[] Returns an array of SitHousing objects
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
    public function findOneBySomeField($value): ?SitHousing
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
