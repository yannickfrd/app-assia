<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Accommodation;
use App\Entity\AccommodationGroup;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method AccommodationGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccommodationGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccommodationGroup[]    findAll()
 * @method AccommodationGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccommodationGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccommodationGroup::class);
    }

    /**
     * Donne la prise en charge avec le groupe et les personnes rattachées.
     *
     * @param int $id // AccommodationGroup
     */
    public function findAccommodationGroupById(int $id): ?AccommodationGroup
    {
        return $this->createQueryBuilder('ag')
            ->select('ag')
            ->leftJoin('ag.createdBy', 'user')->addselect('PARTIAL user.{id, firstname, lastname}')
            ->leftJoin('ag.accommodation', 'a')->addselect('PARTIAL a.{id, name}')
            ->leftJoin('ag.accommodationPeople', 'ap')->addselect('ap')
            ->leftJoin('ap.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate}')
            ->leftJoin('ag.supportGroup', 'sg')->addselect('PARTIAL sg.{id, startDate, endDate}')
            ->leftJoin('ag.groupPeople', 'gp')->addselect('PARTIAL gp.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.service', 'sv')->addselect('PARTIAL sv.{id, name, accommodation}')
            ->leftJoin('gp.rolePeople', 'rp')->addselect('PARTIAL rp.{id, role, head}')
            ->leftJoin('rp.person', 'p1')->addselect('PARTIAL p1.{id, firstname, lastname, birthdate}')

            ->andWhere('ag.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne les prises en charge sur le groupe de places.
     *
     * @return mixed
     */
    public function findAllFromAccommodation(Accommodation $accommodation, $maxResults = 10)
    {
        $query = $this->createQueryBuilder('ag')
            ->select('ag')
            ->leftJoin('ag.accommodationPeople', 'ap')->addselect('PARTIAL ap.{id}')
            ->leftJoin('ag.supportGroup', 'sg')->addselect('PARTIAL sg.{id, startDate, endDate}')
            ->leftJoin('ag.groupPeople', 'gp')->addselect('PARTIAL gp.{id, familyTypology}')
            ->leftJoin('sg.supportPeople', 'sp')->addselect('PARTIAL sp.{id, head, role, startDate, endDate}')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('ag.accommodation = :accommodation')
            ->setParameter('accommodation', $accommodation)

            ->orderBy('ag.startDate', 'DESC')

            ->setMaxResults($maxResults)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        return new Paginator($query);
    }
}
