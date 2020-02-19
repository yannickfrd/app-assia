<?php

namespace App\Repository;

use App\Entity\EvalJusticePerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method EvalJusticePerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvalJusticePerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvalJusticePerson[]    findAll()
 * @method EvalJusticePerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvalJusticePersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvalJusticePerson::class);
    }

    // /**
    //  * @return EvalJusticePerson[] Returns an array of EvalJusticePerson objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?EvalJusticePerson
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
