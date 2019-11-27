<?php

namespace App\Repository;

use App\Entity\SitAdm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SitAdm|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitAdm|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitAdm[]    findAll()
 * @method SitAdm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitAdmRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitAdm::class);
    }

    // /**
    //  * @return SitAdm[] Returns an array of SitAdm objects
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
    public function findOneBySomeField($value): ?SitAdm
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
