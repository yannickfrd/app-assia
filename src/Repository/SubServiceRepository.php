<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\SubService;
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
     * Donne tous les utilisateurs du service.
     *
     * @return mixed
     */
    public function findSubServicesFromService(Service $service)
    {
        return $this->createQueryBuilder('ss')
            ->select('PARTIAL ss.{id, name, phone1, email, disabledAt}')
            ->leftJoin('ss.chief', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('ss.service', 's')->addSelect('s')

            ->where('ss.disabledAt IS NULL')
            ->andWhere('ss.service = :service')
            ->setParameter('service', $service)

            ->orderBy('ss.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la liste des services de l'utilisateur.
     */
    public function getSubServicesFromUserQueryList(CurrentUserService $currentUser): QueryBuilder
    {
        $query = $this->createQueryBuilder('ss')->select('PARTIAL ss.{id, name}')
            ->leftJoin('ss.service', 's')->addSelect('PARTIAL s.{id, name}')

        ->where('ss.disabledAt IS NULL');

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query->orderBy('ss.name', 'ASC');
    }

    public function findSubServicesWithAccommodation(CurrentUserService $currentUser, \DateTime $start, \DateTime $end, Service $service)
    {
        $query = $this->createQueryBuilder('ss')->select('ss')
            ->leftJoin('ss.service', 's')->addSelect('s')
            ->leftJoin('ss.accommodations', 'a')->addSelect('PARTIAL a.{id, name, startDate, endDate, nbPlaces}')

            ->andWhere('s.accommodation = TRUE')
            ->andWhere('a.endDate > :start OR a.endDate IS NULL')->setParameter('start', $start)
            ->andWhere('a.startDate < :end')->setParameter('end', $end);

        if ($service) {
            $query = $query->andWhere('ss.service = :service')
                ->setParameter('service', $service);
        }
        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
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
