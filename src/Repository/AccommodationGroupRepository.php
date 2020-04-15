<?php

namespace App\Repository;

use App\Entity\AccommodationGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

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
     * @param int $id // AccomodationGroup
     */
    public function findOneById(int $id): ?AccommodationGroup
    {
        return $this->createQueryBuilder('gpa')
            ->select('gpa')
            ->leftJoin('gpa.createdBy', 'user')->addselect('PARTIAL user.{id, firstname, lastname}')
            ->leftJoin('gpa.accommodation', 'a')->addselect('PARTIAL a.{id, name}')
            ->leftJoin('gpa.accommodationPeople', 'pa')->addselect('pa')
            ->leftJoin('pa.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname}')
            ->leftJoin('gpa.supportGroup', 'sg')->addselect('PARTIAL sg.{id, startDate, endDate}')
            ->leftJoin('sg.groupPeople', 'gp')->addselect('PARTIAL gp.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.service', 'sv')->addselect('PARTIAL sv.{id, name, accommodation}')
            ->leftJoin('gp.rolePerson', 'rp')->addselect('PARTIAL rp.{id, role, head}')
            ->leftJoin('rp.person', 'p1')->addselect('PARTIAL p1.{id, firstname, lastname}')

            ->andWhere('gpa.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
