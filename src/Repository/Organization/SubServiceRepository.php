<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Form\Model\Admin\OccupancySearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SubService|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubService|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubService[]    findAll()
 * @method SubService[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubServiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SubService::class);
    }

    /**
     * Donne tous les sous-services du service.
     *
     * @return SubService[]|null
     */
    public function findSubServicesOfService(Service $service)
    {
        return $this->createQueryBuilder('ss')
            ->select('PARTIAL ss.{id, name, phone1, email, disabledAt}')
            ->leftJoin('ss.chief', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('ss.service', 's')->addSelect('s')

            // ->where('ss.disabledAt IS NULL')
            ->andWhere('ss.service = :service')
            ->setParameter('service', $service)

            ->orderBy('ss.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les sous-services du service.
     *
     * @return mixed
     */
    public function getSubServicesOfService(Service $service)
    {
        return $this->getSubServicesOfServiceQueryBuilder($service)
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la liste des sous-services du service.
     */
    public function getSubServicesOfServiceQueryBuilder(Service $service): QueryBuilder
    {
        return $this->createQueryBuilder('ss')->select('PARTIAL ss.{id, name, disabledAt}')
            ->leftJoin('ss.service', 's')->addSelect('PARTIAL s.{id, name}')

            ->where('ss.disabledAt IS NULL')
            ->andWhere('s.disabledAt IS NULL')
            ->andWhere('ss.service = :service')
            ->setParameter('service', $service)

            ->orderBy('ss.name', 'ASC');
    }

    /**
     * Donne la liste des sous-services de l'utilisateur.
     */
    public function getSubServicesOfUserQueryBuilder(CurrentUserService $currentUser, Service $service = null): QueryBuilder
    {
        $query = $this->createQueryBuilder('ss')->select('PARTIAL ss.{id, name}')
            ->leftJoin('ss.service', 's')->addSelect('PARTIAL s.{id, name}')

        ->where('ss.disabledAt IS NULL')
        ->andWhere('s.disabledAt IS NULL');

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($service) {
            $query = $query->andWhere('s.id = :service')
                ->setParameter('service', $service);
        }

        return $query->orderBy('ss.name', 'ASC');
    }

    public function findSubServicesWithPlace(OccupancySearch $search, CurrentUserService $currentUser, ?Service $service = null)
    {
        $query = $this->createQueryBuilder('ss')->select('ss')
            ->leftJoin('ss.service', 's')->addSelect('s')
            ->leftJoin('ss.places', 'pl')->addSelect('PARTIAL pl.{id, name, startDate, endDate, nbPlaces}')

            ->andWhere('s.place = TRUE')
            ->andWhere('pl.endDate > :start OR pl.endDate IS NULL')->setParameter('start', $search->getStart())
            ->andWhere('pl.startDate < :end')->setParameter('end', $search->getEnd());

        if ($search->getPole()) {
            $query = $query->andWhere('s.pole = :pole')
                ->setParameter('pole', $search->getPole());
        }

        if ($service) {
            $query = $query->andWhere('ss.service = :service')
                ->setParameter('service', $service);
        }
        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query
            ->orderBy('ss.name', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    // /**
    //  * @return SubService[] Returns an array of SubService objects
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
    public function findOneBySomeField($value): ?SubService
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
