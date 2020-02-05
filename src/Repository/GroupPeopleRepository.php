<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\GroupPeople;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method GroupPeople|null find($id, $lockMode = null, $lockVersion = null)
 * @method GroupPeople|null findOneBy(array $criteria, array $orderBy = null)
 * @method GroupPeople[]    findAll()
 * @method GroupPeople[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupPeopleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GroupPeople::class);
    }

    /**
     * Donne le groupe de personnes
     *
     * @param int $id
     * @return GroupPeople|null
     */
    public function findGroupPeopleById($id): ?GroupPeople
    {
        return $this->createQueryBuilder("g")
            ->select("g")
            ->leftJoin("g.rolePerson", "r")->addselect("PARTIAL r.{id, role, head}")
            ->leftJoin("r.person", "p")->addselect("p")
            ->leftJoin("g.supports", "sg")->addselect("PARTIAL sg.{id, status, startDate, endDate, updatedAt}")
            ->leftJoin("sg.referent", "ref")->addselect("PARTIAL ref.{id, firstname, lastname, email, phone}")
            ->leftJoin("sg.service", "s")->addselect("PARTIAL s.{id, name, email, phone}")
            ->leftJoin("s.pole", "pole")->addselect("PARTIAL pole.{id, name}")

            ->andWhere("g.id = :id")
            ->setParameter("id", $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
