<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\User;
use App\Form\Model\ServiceSearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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
     * Retourne tous les services.
     */
    public function findAllServicesQuery(ServiceSearch $serviceSearch): Query
    {
        $query = $this->createQueryBuilder('s')->select('s')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id,name}');

        if ($serviceSearch->getName()) {
            $query->andWhere('s.name LIKE :name')
                ->setParameter('name', $serviceSearch->getName().'%');
        }
        if ($serviceSearch->getPhone()) {
            $query->andWhere('s.phone = :phone')
                ->setParameter('phone', $serviceSearch->getPhone());
        }
        if ($serviceSearch->getPole()) {
            $query = $query->andWhere('p.id = :pole_id')
                ->setParameter('pole_id', $serviceSearch->getPole());
        }

        return $query->orderBy('s.name', 'ASC')->getQuery();
    }

    /**
     * Donne tous les services à exporter.
     *
     * @return mixed
     */
    public function findServicesToExport(ServiceSearch $serviceSearch)
    {
        return $this->findAllServicesQuery($serviceSearch)->getResult();
    }

    /**
     * Donne la liste des services de l'utilisateur.
     */
    public function getServicesFromUserQueryList(CurrentUserService $currentUser): QueryBuilder
    {
        $query = $this->createQueryBuilder('s')->select('PARTIAL s.{id, name}');

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query->orderBy('s.name', 'ASC');
    }

    /**
     * Donne tous les services de l'utilisateur.
     *
     * @return mixed
     */
    public function findAllServicesFromUser(User $user)
    {
        return $this->createQueryBuilder('s')
            ->select('PARTIAL s.{id, name, email, phone}')
            ->leftJoin('s.pole', 'p')->addselect('PARTIAL p.{id, name}')
            ->leftJoin('s.serviceUser', 'su')->addselect('su')

            ->andWhere('su.user = :user')
            ->setParameter('user', $user)

            ->orderBy('s.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne un service.
     *
     * @param int $id from Service
     */
    public function getFullService(int $id): ?Service
    {
        return $this->createQueryBuilder('s')->select('s')
            ->leftJoin('s.pole', 'p')->addselect('PARTIAL p.{id, name}')

            ->leftJoin('s.chief', 'chief')->addselect('PARTIAL chief.{id, firstname, lastname, status, phone, email}')

            ->leftJoin('s.serviceDevices', 'sd')->addselect('sd')
            ->leftJoin('sd.device', 'd')->addselect('PARTIAL d.{id, name}')

            ->leftJoin('s.accommodations', 'a')->addselect('a')

            ->leftJoin('s.serviceUser', 'su')->addselect('su')
            ->leftJoin('su.user', 'u')->addselect('PARTIAL u.{id, firstname, lastname, status, phone, email}')

            ->where('s.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
