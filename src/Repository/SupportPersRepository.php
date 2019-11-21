<?php

namespace App\Repository;

use App\Entity\SupportPers;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SupportPers|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportPers|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportPers[]    findAll()
 * @method SupportPers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportPersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportPers::class);
    }

    // /**
    //  * @return SupportPers[] Returns an array of SupportPers objects
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
    public function findOneBySomeField($value): ?SupportPers
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
