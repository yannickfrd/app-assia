<?php

namespace App\Repository;

use App\Entity\SitProf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SitProf|null find($id, $lockMode = null, $lockVersion = null)
 * @method SitProf|null findOneBy(array $criteria, array $orderBy = null)
 * @method SitProf[]    findAll()
 * @method SitProf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SitProfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SitProf::class);
    }

    // /**
    //  * @return SitProf[] Returns an array of SitProf objects
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
    public function findOneBySomeField($value): ?SitProf
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
