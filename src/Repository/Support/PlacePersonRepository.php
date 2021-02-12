<?php

namespace App\Repository\Support;

use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Support\PlacePerson;
use App\Form\Model\Admin\OccupancySearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlacePerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlacePerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlacePerson[]    findAll()
 * @method PlacePerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlacePersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlacePerson::class);
    }

    /**
     * Donne la prise en charge de la personne.
     */
    public function findPlacePersonById(int $id): ?PlacePerson
    {
        return $this->createQueryBuilder('pp')->select('pp')
            ->leftJoin('pp.createdBy', 'user')->addSelect('user')
            ->leftJoin('pp.person', 'p')->addSelect('p')
            ->leftJoin('pp.placeGroup', 'pg')->addSelect('pg')
            ->leftJoin('pg.supportGroup', 'sg')->addSelect('sg')

            ->andWhere('pp.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne les prises en charge des personnes.
     *
     * @return PlacePerson[]|null
     */
    public function findPlacePeople(OccupancySearch $search, CurrentUserService $currentUser, Service $service = null, SubService $subService = null): ?array
    {
        $query = $this->createQueryBuilder('pp')->select('pp')
            ->leftJoin('pp.placeGroup', 'pg')->addSelect('PARTIAL pg.{id, place}')
            ->leftJoin('pg.place', 'pl')->addSelect('pl')
            ->leftJoin('pl.service', 's')->addSelect('PARTIAL s.{id}')
            ->leftJoin('pl.subService', 'ss')->addSelect('PARTIAL ss.{id}')

            ->andWhere('pg.endDate > :start OR pg.endDate IS NULL')->setParameter('start', $search->getStart())
            ->andWhere('pg.startDate < :end')->setParameter('end', $search->getEnd());

        if ($service) {
            $query = $query->andWhere('pl.service = :service')
                ->setParameter('service', $service);
        }
        if ($subService) {
            $query->andWhere('pl.subService = :subService')
                ->setParameter('subService', $subService);
        }
        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
