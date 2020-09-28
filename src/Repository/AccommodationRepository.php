<?php

namespace App\Repository;

use App\Entity\Accommodation;
use App\Entity\Service;
use App\Entity\SubService;
use App\Entity\SupportGroup;
use App\Form\Model\AccommodationSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Accommodation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Accommodation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Accommodation[]    findAll()
 * @method Accommodation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccommodationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Accommodation::class);
    }

    /**
     * Retourne toutes les places.
     */
    public function findAllAccommodationsQuery(AccommodationSearch $search = null): Query
    {
        $query = $this->getAccommodations();

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->orderBy('a.name', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Retourne toutes les places pour l'export.
     *
     * @return mixed
     */
    public function findAccommodationsToExport(AccommodationSearch $search = null)
    {
        $query = $this->getAccommodations();

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->orderBy('a.name', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la liste des groupes de places.
     */
    public function getAccommodationsQueryList(?int $serviceId = null, ?int $subServiceId = null): QueryBuilder
    {
        $query = $this->createQueryBuilder('a')
            ->select('PARTIAL a.{id, name, service, address, city, zipcode, commentLocation, locationId, lat, lon}');

        if ($subServiceId) {
            $query->where('a.subService = :subService')
            ->setParameter('subService', $subServiceId);
        } else {
            $query->where('a.service = :service')
            ->setParameter('service', $serviceId);
        }

        return  $query->andWhere('a.endDate IS NULL')
            ->orWhere('a.endDate > :date')
            ->setParameter('date', new \Datetime())

            ->orderBy('a.name', 'ASC');
    }

    /**
     * Donne toutes les places du service.
     *
     * @return mixed
     */
    public function findAccommodationsFromService(Service $service)
    {
        return $this->createQueryBuilder('a')->select('a')
            ->leftJoin('a.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('a.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->where('a.service = :service')
            ->setParameter('service', $service)

            ->orderBy('a.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les places du sous-service.
     *
     * @return mixed
     */
    public function findAccommodationsFromSubService(SubService $subService)
    {
        return $this->createQueryBuilder('a')->select('a')
            ->innerJoin('a.device', 'd')->addSelect('PARTIAL d.{id,name}')

            ->where('a.subService = :subService')
            ->setParameter('subService', $subService)

            ->orderBy('a.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les groupes de places pour les taux d'occupation.
     *
     * @return mixed
     */
    public function findAccommodationsForOccupancy($currentUser, Service $service = null, SubService $subService = null)
    {
        $query = $this->createQueryBuilder('a')->select('a')
            ->innerJoin('a.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('a.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->innerJoin('a.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->where('a.startDate IS NOT NULL');

        if ($service) {
            $query->andWhere('a.service = :service')
                ->setParameter('service', $service);
        }
        if ($subService) {
            $query->andWhere('a.subService = :subService')
                ->setParameter('subService', $subService);
        }
        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('a.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query
            ->addOrderBy('a.service', 'ASC')
            ->addOrderBy('a.name', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne le groupe de places actuel du suivi.
     */
    public function findCurrentAccommodationOfSupport(SupportGroup $supportGroup): ?Accommodation
    {
        return $this->createQueryBuilder('a')->select('PARTIAL a.{id, rentAmt}')
            ->leftJoin('a.accommodationGroups', 'ag')->addSelect('PARTIAL ag.{id, supportGroup}')
            ->andWhere('ag.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)
            ->orderBy('ag.startDate', 'DESC')
            ->setMaxResults(1)
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    protected function getAccommodations()
    {
        return $this->createQueryBuilder('a')->select('a')
            ->leftJoin('a.device', 'd')->addSelect('PARTIAL d.{id,name}')
            ->leftJoin('a.service', 's')->addSelect('PARTIAL s.{id,name}')
            ->leftJoin('a.subService', 'ss')->addSelect('PARTIAL ss.{id,name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id,name}')
            ->leftJoin('a.accommodationGroups', 'ag')->addSelect('PARTIAL ag.{id,startDate, endDate}')
            ->leftJoin('ag.accommodationPeople', 'ap')->addSelect('PARTIAL ap.{id,startDate, endDate}');
        // ->leftJoin("ap.person", "p")->addSelect("PARTIAL p.{id,firstname, lastname}");
    }

    /**
     * Filtre la recherche.
     */
    protected function filter($query, AccommodationSearch $search)
    {
        if ($search->getName()) {
            $query->andWhere('a.name LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getCity()) {
            $query->andWhere('a.city LIKE :city')
                ->setParameter('city', '%'.$search->getCity().'%');
        }
        if ($search->getNbPlaces()) {
            $query->andWhere('a.nbPlaces = :nbPlaces')
                ->setParameter('nbPlaces', $search->getNbPlaces());
        }

        $supportDates = $search->getSupportDates();

        if (1 == $supportDates) {
            if ($search->getStart()) {
                $query->andWhere('a.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('a.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 == $supportDates) {
            if ($search->getStart()) {
                if ($search->getStart()) {
                    $query->andWhere('a.endDate >= :start')
                        ->setParameter('start', $search->getStart());
                }
                if ($search->getEnd()) {
                    $query->andWhere('a.endDate <= :end')
                        ->setParameter('end', $search->getEnd());
                }
            }
        }
        if (3 == $supportDates || !$supportDates) {
            if ($search->getStart()) {
                $query->andWhere('a.endDate >= :start OR a.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('a.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search->getPole()) {
            $query->andWhere('pole.id = :pole_id')
                ->setParameter('pole_id', $search->getPole());
        }

        if ($search->getServices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('s.id', $service));
            }
            $query->andWhere($orX);
        }
        if ($search->getSubServices() && $search->getSubServices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getSubServices() as $subService) {
                $orX->add($expr->eq('ss.id', $subService));
            }
            $query->andWhere($orX);
        }
        if ($search->getDevices() && $search->getDevices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('d.id', $device));
            }
            $query->andWhere($orX);
        }

        return $query;
    }
}
