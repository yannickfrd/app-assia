<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Admin\OccupancySearch;
use App\Form\Model\Organization\PlaceSearch;
use App\Form\Utils\Choices;
use App\Repository\Traits\QueryTrait;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Place|null find($id, $lockMode = null, $lockVersion = null)
 * @method Place|null findOneBy(array $criteria, array $orderBy = null)
 * @method Place[]    findAll()
 * @method Place[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceRepository extends ServiceEntityRepository
{
    use QueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Place::class);
    }

    /**
     * Retourne toutes les places.
     */
    public function findPlacesQuery(PlaceSearch $search = null, CurrentUserService $currentUser = null): Query
    {
        $qb = $this->getPlacesAlterQueryBuilder();

        if ($currentUser && !$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('pl.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($search) {
            $qb = $this->filter($qb, $search);
        }

        return $qb
            ->orderBy('pl.name', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Retourne toutes les places pour l'export.
     *
     * @return Place[]|null
     */
    public function findPlacesToExport(PlaceSearch $search = null): ?array
    {
        $qb = $this->getPlacesAlterQueryBuilder();

        if ($search) {
            $qb = $this->filter($qb, $search);
        }

        return $qb
            ->orderBy('pl.name', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    /**
     * Donne la liste des groupes de places.
     */
    public function getPlacesQueryBuilder(?Service $service = null, ?SubService $subService = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('pl')->select('pl')

            ->where('pl.disabledAt IS NULL');

        if ($subService) {
            $qb->andWhere('pl.subService = :subService')
            ->setParameter('subService', $subService);
        } else {
            $qb->andWhere('pl.service = :service')
            ->setParameter('service', $service);
        }

        return $qb
            ->andWhere('pl.endDate IS NULL')
            ->orWhere('pl.endDate > :date')
            ->setParameter('date', new \Datetime())

            ->orderBy('pl.name', 'ASC')
        ;
    }

    /**
     * Donne la liste des hôtels des services d'accompagnement à l'hôtel.
     */
    public function getHotelPlacesQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('pl')->select('PARTIAL pl.{id, name}')
            ->leftJoin('pl.service', 's')->addSelect('PARTIAL s.{id, name, type}')

            ->where('s.type = :type')
            ->setParameter('type', Service::SERVICE_TYPE_HOTEL)
            ->orderBy('pl.name', 'ASC');
    }

    /**
     * Donne toutes les places du service.
     *
     * @return Place[]|null
     */
    public function findPlacesOfService(Service $service): ?array
    {
        return $this->createQueryBuilder('pl')->select('pl')
            ->leftJoin('pl.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('pl.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->where('pl.service = :service')
            ->setParameter('service', $service)

            ->orderBy('pl.name', 'ASC')

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les places du sous-service.
     *
     * @return Place[]|null
     */
    public function findPlacesOfSubService(SubService $subService): ?array
    {
        return $this->createQueryBuilder('pl')->select('pl')
            ->innerJoin('pl.device', 'd')->addSelect('PARTIAL d.{id,name}')

            ->where('pl.subService = :subService')
            ->setParameter('subService', $subService)

            ->orderBy('pl.name', 'ASC')

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les groupes de places pour les taux d'occupation.
     *
     * @return Place[]|null
     */
    public function findPlacesForOccupancy(OccupancySearch $search, $currentUser, Service $service = null, SubService $subService = null): ?array
    {
        $qb = $this->createQueryBuilder('pl')->select('pl')
            ->innerJoin('pl.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('pl.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->innerJoin('pl.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->andWhere('pl.endDate > :start OR pl.endDate IS NULL')->setParameter('start', $search->getStart())
            ->andWhere('pl.startDate < :end')->setParameter('end', $search->getEnd());

        if ($search->getPole()) {
            $qb->andWhere('s.pole = :pole')
                ->setParameter('pole', $search->getPole());
        }

        if ($service) {
            $qb->andWhere('pl.service = :service')
                ->setParameter('service', $service);
        }
        if ($subService) {
            $qb->andWhere('pl.subService = :subService')
                ->setParameter('subService', $subService);
        }
        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('pl.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $qb
            ->addOrderBy('pl.service', 'ASC')
            ->addOrderBy('pl.name', 'ASC')

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne le groupe de places actuel du suivi.
     */
    public function findCurrentPlaceOfSupport(SupportGroup $supportGroup): ?Place
    {
        return $this->createQueryBuilder('pl')->select('PARTIAL pl.{id, rentAmt}')
            ->leftJoin('pl.placeGroups', 'pg')->addSelect('PARTIAL pg.{id, supportGroup}')

            ->andWhere('pg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)

            ->orderBy('pg.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    protected function getPlacesAlterQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('pl')->select('pl')
            ->leftJoin('pl.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('pl.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('pl.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('pl.placeGroups', 'pg')->addSelect('PARTIAL pg.{id, startDate, endDate}')
            ->leftJoin('pg.placePeople', 'pp')->addSelect('PARTIAL pp.{id, startDate, endDate}');
        // ->leftJoin("pp.person", "p")->addSelect("PARTIAL p.{id, firstname, lastname}");
    }

    /**
     * Filtre la recherche.
     */
    protected function filter(QueryBuilder $qb, PlaceSearch $search): QueryBuilder
    {
        if ($search->getName()) {
            $qb->andWhere('pl.name LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getCity()) {
            $qb->andWhere('pl.city LIKE :city')
                ->setParameter('city', '%'.$search->getCity().'%');
        }
        if ($search->getNbPlaces()) {
            $qb->andWhere('pl.nbPlaces = :nbPlaces')
                ->setParameter('nbPlaces', $search->getNbPlaces());
        }

        $supportDates = $search->getSupportDates();

        if (1 === $supportDates) {
            if ($search->getStart()) {
                $qb->andWhere('pl.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $qb->andWhere('pl.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 === $supportDates) {
            if ($search->getStart()) {
                if ($search->getStart()) {
                    $qb->andWhere('pl.endDate >= :start')
                        ->setParameter('start', $search->getStart());
                }
                if ($search->getEnd()) {
                    $qb->andWhere('pl.endDate <= :end')
                        ->setParameter('end', $search->getEnd());
                }
            }
        }
        if (3 === $supportDates || !$supportDates) {
            if ($search->getStart()) {
                $qb->andWhere('pl.endDate >= :start OR pl.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $qb->andWhere('pl.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if (Choices::DISABLED === $search->getDisabled()) {
            $qb->andWhere('pl.disabledAt IS NOT NULL');
        } elseif (Choices::ACTIVE === $search->getDisabled()) {
            $qb->andWhere('pl.disabledAt IS NULL');
        }

        $qb = $this->addPolesFilter($qb, $search);
        $qb = $this->addServicesFilter($qb, $search);
        $qb = $this->addSubServicesFilter($qb, $search, 'ss.id');
        $qb = $this->addDevicesFilter($qb, $search, 'd.id');

        return $qb;
    }
}
