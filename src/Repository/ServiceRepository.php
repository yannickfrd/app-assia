<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Service;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /**
     * Retourne tous services
     * @return Query
     */
    public function findAllServicesQuery($serviceSearch): Query
    {
        $query =  $this->createQueryBuilder("s")
            ->select("s")
            ->leftJoin("s.pole", "p")
            ->addselect("p");
        if ($serviceSearch->getName()) {
            $query->andWhere("s.name LIKE :name")
                ->setParameter("name", $serviceSearch->getName() . '%');
        }
        if ($serviceSearch->getPhone()) {
            $query->andWhere("s.phone = :phone")
                ->setParameter("phone", $serviceSearch->getPhone());
        }
        if ($serviceSearch->getPole()) {
            $query = $query->andWhere("p.id = :pole_id")
                ->setParameter("pole_id", $serviceSearch->getPole());
        }
        return $query->orderBy("s.name", "ASC")
            ->getQuery();
    }


    // /**
    //  * @return Service[] Returns an array of Service objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
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
    public function findOneBySomeField($value): ?Service
    {
        return $this->createQueryBuilder('d')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
