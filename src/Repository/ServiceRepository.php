<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Service;
use Doctrine\Persistence\ManagerRegistry;
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

    public function findServicesToExport($serviceSearch)
    {
        $query = $this->findAllServicesQuery($serviceSearch);
        return $query->getResult();
    }
}
