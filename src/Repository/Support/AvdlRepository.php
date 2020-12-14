<?php

namespace App\Repository\Support;

use App\Entity\Support\Avdl;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Avdl|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avdl|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avdl[]    findAll()
 * @method Avdl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvdlRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avdl::class);
    }

    // /**
    //  * @return Avdl[] Returns an array of Avdl objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Avdl
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
