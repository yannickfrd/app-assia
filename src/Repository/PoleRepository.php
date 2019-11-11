<?php

namespace App\Repository;

use App\Entity\Pole;
use Doctrine\ORM\Query;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Pole|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pole|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pole[]    findAll()
 * @method Pole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pole::class);
    }

    /**
     * Retourne toutes les personnes
     * @return Query
     */
    public function findAllPolesQuery(): Query
    {
        $query =  $this->createQueryBuilder("p")
            ->select("p");
        return $query->orderBy("p.name", "ASC")
            ->getQuery();
    }

    // /**
    //  * @return Pole[] Returns an array of Pole objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Pole
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
