<?php

namespace App\Repository;

use App\Entity\RolePerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method RolePerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method RolePerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method RolePerson[]    findAll()
 * @method RolePerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolePersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RolePerson::class);
    }

    // /**
    //  * @return RolePerson[] Returns an array of RolePerson objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?RolePerson
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
