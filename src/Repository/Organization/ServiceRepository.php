<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Form\Model\Admin\OccupancySearch;
use App\Form\Model\Admin\ServiceIndicatorsSearch;
use App\Form\Model\Organization\ServiceSearch;
use App\Form\Utils\Choices;
use App\Repository\Traits\QueryTrait;
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
    use QueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    /**
     * Retourne tous les services.
     */
    public function findServicesQuery(ServiceSearch $search, User $user = null): Query
    {
        $query = $this->createQueryBuilder('s')->select('s')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id, name}');

        if ($user && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $query->where('s.disabledAt IS NULL');
        }

        if ($search->getName()) {
            $query->andWhere('s.name LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getPhone()) {
            $query->andWhere('s.phone1 LIKE :phone')
                    ->setParameter('phone', '%'.$search->getPhone().'%');
        }
        if ($search->getPole()) {
            $query = $query->andWhere('p.id = :pole')
                ->setParameter('pole', $search->getPole());
        }
        if (Choices::DISABLED === $search->getDisabled()) {
            $query->andWhere('s.disabledAt IS NOT NULL');
        } elseif (Choices::ACTIVE === $search->getDisabled()) {
            $query->andWhere('s.disabledAt IS NULL');
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
        return $this->findServicesQuery($serviceSearch)->getResult();
    }

    /**
     * Donne la liste des services de l'utilisateur.
     */
    public function getServicesOfUserQueryBuilder(CurrentUserService $currentUser, string $dataClass = null): QueryBuilder
    {
        $query = $this->createQueryBuilder('s')->select('PARTIAL s.{id, name, type, preAdmission, coefficient}')

            ->where('s.disabledAt IS NULL');

        if ($dataClass) {
            $query = $this->filterByServiceType($query, $dataClass);
        }

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query->orderBy('s.name', 'ASC');
    }

    public function findServicesWithPlace(OccupancySearch $search, CurrentUserService $currentUser, Device $device = null)
    {
        $query = $this->createQueryBuilder('s')->select('s')
            ->leftJoin('s.subServices', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('s.places', 'pl')->addSelect('PARTIAL pl.{id, name, startDate, endDate, nbPlaces}')
            ->leftJoin('s.serviceDevices', 'sd')->addSelect('sd')

            ->andWhere('s.place = TRUE')
            ->andWhere('pl.endDate > :start OR pl.endDate IS NULL')->setParameter('start', $search->getStart())
            ->andWhere('pl.startDate < :end')->setParameter('end', $search->getEnd());

        if ($search->getPole()) {
            $query = $query->andWhere('s.pole = :pole')
                ->setParameter('pole', $search->getPole());
        }

        if ($device) {
            $query = $query->andWhere('sd.device = :device')
                ->setParameter('device', $device);
        }
        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query
            ->orderBy('s.name', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les services de l'utilisateur.
     *
     * @return mixed
     */
    public function findServicesOfUser(User $user)
    {
        return $this->createQueryBuilder('s')
            ->select('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id, name}')
            ->leftJoin('s.serviceUser', 'su')->addSelect('su')

            ->where('s.disabledAt IS NULL')

            ->andWhere('su.user = :user')
            ->setParameter('user', $user)

            ->orderBy('s.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les services de l'utilisateur.
     *
     * @return mixed
     */
    public function findServicesAndSubServicesOfUser(User $user)
    {
        $query = $this->createQueryBuilder('s')->select('PARTIAL s.{id, name}')
            ->leftJoin('s.subServices', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('s.serviceDevices', 'sd')->addSelect('sd')
            ->leftJoin('sd.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('s.serviceUser', 'su')->addSelect('su')

            ->where('s.disabledAt IS NULL');

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $query->andWhere('su.user = :user')
                ->setParameter('user', $user);
        }

        return $query->orderBy('s.name', 'ASC')

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
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id, name}')
            ->leftJoin('s.chief', 'chief')->addSelect('PARTIAL chief.{id, firstname, lastname, status, phone1, email}')
            ->leftJoin('s.serviceDevices', 'sd')->addSelect('sd')
            ->leftJoin('sd.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->where('s.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    public function findServices(ServiceIndicatorsSearch $search)
    {
        $query = $this->createQueryBuilder('s')->select('s');

        $query = $this->addPolesFilter($query, $search);

        return $query->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
