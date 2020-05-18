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
        return $this->createQueryBuilder('gpa')
            ->select('gpa')
            ->leftJoin('gpa.createdBy', 'user')->addselect('PARTIAL user.{id, firstname, lastname}')
            ->leftJoin('gpa.accommodation', 'a')->addselect('PARTIAL a.{id, name}')
            ->leftJoin('gpa.accommodationPeople', 'pa')->addselect('pa')
            ->leftJoin('pa.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate}')
            ->leftJoin('gpa.supportGroup', 'sg')->addselect('PARTIAL sg.{id, startDate, endDate}')
            ->leftJoin('gpa.groupPeople', 'gp')->addselect('PARTIAL gp.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.service', 'sv')->addselect('PARTIAL sv.{id, name, accommodation}')
            ->leftJoin('gp.rolePeople', 'rp')->addselect('PARTIAL rp.{id, role, head}')
            ->leftJoin('rp.person', 'p1')->addselect('PARTIAL p1.{id, firstname, lastname, birthdate}')

            ->andWhere('gpa.id = :id')
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
        $query = $this->createQueryBuilder('gpa')
            ->select('gpa')
            ->leftJoin('gpa.accommodationPeople', 'ap')->addselect('PARTIAL ap.{id}')
            ->leftJoin('gpa.supportGroup', 'sg')->addselect('PARTIAL sg.{id, startDate, endDate}')
            ->leftJoin('gpa.groupPeople', 'gp')->addselect('PARTIAL gp.{id, familyTypology}')
            ->leftJoin('sg.supportPeople', 'sp')->addselect('PARTIAL sp.{id, head, role, startDate, endDate}')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('gpa.accommodation = :accommodation')
            ->setParameter('accommodation', $accommodation)

            ->orderBy('gpa.startDate', 'DESC')

            ->setMaxResults($maxResults)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        return new Paginator($query);
    }
}
