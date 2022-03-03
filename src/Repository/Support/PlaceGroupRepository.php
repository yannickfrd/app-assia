<?php

namespace App\Repository\Support;

use App\Entity\Organization\Place;
use App\Entity\Support\PlaceGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PlaceGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlaceGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlaceGroup[]    findAll()
 * @method PlaceGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlaceGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaceGroup::class);
    }

    /**
     * Donne la prise en charge avec le groupe et les personnes rattachées.
     *
     * @param int $id // PlaceGroup
     */
    public function findPlaceGroupById(int $id): ?PlaceGroup
    {
        return $this->createQueryBuilder('pg')
            ->leftJoin('pg.createdBy', 'user')->addSelect('PARTIAL user.{id, firstname, lastname}')
            ->leftJoin('pg.place', 'pl')->addSelect('PARTIAL pl.{id, name}')
            ->leftJoin('pg.placePeople', 'pp')->addSelect('pp')
            ->leftJoin('pp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate}')
            ->leftJoin('pg.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, startDate, endDate}')
            ->leftJoin('pp.supportPerson', 'sp')->addSelect('PARTIAL sp.{id, head, role, startDate, endDate}')
            ->leftJoin('pg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.service', 'sv')->addSelect('PARTIAL sv.{id, name, place}')
            ->leftJoin('g.rolePeople', 'rp')->addSelect('PARTIAL rp.{id, role, head}')
            ->leftJoin('rp.person', 'p1')->addSelect('PARTIAL p1.{id, firstname, lastname, birthdate}')

            ->andWhere('pg.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult()
        ;
    }

    /**
     * Donne les prises en charge sur le groupe de places.
     *
     * @return PlaceGroup[]|null
     */
    public function findAllPlaceGroups(Place $place, $maxResults = 10): array
    {
        return $this->createQueryBuilder('pg')
            ->leftJoin('pg.placePeople', 'pp')->addSelect('PARTIAL pg.{id}')
            ->leftJoin('pg.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, startDate, endDate}')
            ->leftJoin('pg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head, role, startDate, endDate}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('sp.head = TRUE')
            ->andWhere('pg.place = :place')
            ->setParameter('place', $place)

            ->addOrderBy('pg.startDate', 'DESC')

            ->setMaxResults($maxResults)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }
}
