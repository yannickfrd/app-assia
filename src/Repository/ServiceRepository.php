<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Service;
use Doctrine\ORM\Query;
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
            ->leftJoin("s.pole", "p")->addSelect("PARTIAL p.{id,name}");

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

    /** 
     * Donne la liste des services
     */
    public function getServicesQueryList($currentUser)
    {
        $query =  $this->createQueryBuilder("s")
            ->select("PARTIAL s.{id, name}");

        if (!$currentUser->isRole("ROLE_SUPER_ADMIN")) {
            $query = $query->andWhere("s.id IN (:services)")
                ->setParameter("services", $currentUser->getServices());
        }
        return $query->orderBy("s.name", "ASC");
    }

    /**
     * Donne tous les services de l'utilisateur
     *
     */
    public function findAllServicesFromUser(User $user)
    {
        return $this->createQueryBuilder("s")
            ->select("PARTIAL s.{id, name, email, phone}")
            ->leftJoin("s.pole", "p")->addselect("PARTIAL p.{id, name}")
            ->leftJoin("s.serviceUser", "su")->addselect("su")

            ->andWhere("su.user = :user")
            ->setParameter("user", $user)

            ->orderBy("s.name", "ASC")

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
