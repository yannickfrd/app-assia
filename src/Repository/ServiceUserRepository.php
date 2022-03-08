<?php

namespace App\Repository;

use App\Entity\ServiceUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServiceUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceUser[]    findAll()
 * @method ServiceUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceUser::class);
    }

    // /**
    //  * @return ServiceUser[] Returns an array of ServiceUser objects
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
    public function findOneBySomeField($value): ?ServiceUser
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
