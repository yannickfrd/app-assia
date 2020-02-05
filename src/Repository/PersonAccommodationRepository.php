<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\PersonAccommodation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method PersonAccommodation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonAccommodation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonAccommodation[]    findAll()
 * @method PersonAccommodation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonAccommodationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonAccommodation::class);
    }

    /**
     * Donne la prise en charge de la personne
     *
     * @param int $id
     * @return PersonAccommodation|null
     */
    public function findOneById($id): ?PersonAccommodation
    {
        return $this->createQueryBuilder("pa")
            ->select("pa")

            ->leftJoin("pa.createdBy", "user")
            ->addselect("user")

            ->leftJoin("pa.person", "p")
            ->addselect("p")

            ->leftJoin("pa.groupPeopleAccommodation", "gpa")
            ->addselect("gpa")
            ->leftJoin("gpa.supportGroup", "sg")
            ->addselect("sg")

            ->andWhere("pa.id = :id")
            ->setParameter("id", $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
