<?php

namespace App\Repository\Support;

use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Entity\Support\PlacePerson;
use App\Form\Model\Admin\OccupancySearch;
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

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne les prises en charge des personnes.
     *
     * @return PlacePerson[]
     */
    public function findPlacePeople(OccupancySearch $search, User $user, Service $service = null, SubService $subService = null): array
    {
        $qb = $this->createQueryBuilder('pp')->select('pp')
            ->leftJoin('pp.placeGroup', 'pg')->addSelect('PARTIAL pg.{id, place}')
            ->leftJoin('pg.place', 'pl')->addSelect('pl')
            ->leftJoin('pl.service', 's')->addSelect('PARTIAL s.{id}')
            ->leftJoin('pl.subService', 'ss')->addSelect('PARTIAL ss.{id}')

            ->andWhere('pp.endDate > :start OR pp.endDate IS NULL')
            ->setParameter('start', $search->getStart())
            ->andWhere('pp.startDate < :end')
            ->setParameter('end', $search->getEnd());

        if ($service) {
            $qb->andWhere('pl.service = :service')
                ->setParameter('service', $service);
        }
        if ($subService) {
            $qb->andWhere('pl.subService = :subService')
                ->setParameter('subService', $subService);
        }
        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('s.id IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        return $qb
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
