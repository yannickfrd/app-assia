<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\SupportGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SupportGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportGroup[]    findAll()
 * @method SupportGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SupportGroup::class);
    }

    /**
     * @return Query
     */
    // Donne les suivis sociaux
    public function findAllSupports($groupPeopleSearch): Query
    {
        $query =  $this->createQueryBuilder("sg")
            ->select("sg")
            ->leftJoin("sg.service", "s")
            ->addselect("s")
            ->leftJoin("sg.supportPerson", "sp")
            ->addselect("sp")
            ->leftJoin("sg.groupPeople", "g")
            ->addselect("g")
            ->leftJoin("g.rolePerson", "r")
            ->addselect("r")
            ->leftJoin("r.person", "p")
            ->addselect("p")
            ->andWhere("r.head = TRUE");
        if ($groupPeopleSearch->getFirstname()) {
            $query->andWhere("p.firstname LIKE :firstname")
                ->setParameter("firstname", $groupPeopleSearch->getFirstname() . '%');
        }
        if ($groupPeopleSearch->getLastname()) {
            $query->andWhere("p.lastname LIKE :lastname")
                ->setParameter("lastname", $groupPeopleSearch->getLastname() . '%');
        }
        if ($groupPeopleSearch->getBirthdate()) {
            $query->andWhere("p.birthdate = :birthdate")
                ->setParameter("birthdate", $groupPeopleSearch->getBirthdate());
        }
        if ($groupPeopleSearch->getFamilyTypology()) {
            $query->andWhere("g.familyTypology = :familyTypology")
                ->setParameter("familyTypology", $groupPeopleSearch->getFamilyTypology());
        }
        if ($groupPeopleSearch->getNbPeople()) {
            $query->andWhere("g.nbPeople = :nbPeople")
                ->setParameter("nbPeople", $groupPeopleSearch->getNbPeople());
        }
        return $query->orderBy("sg.startDate", "DESC")
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }
    // /**
    //  * @return SupportGroup[] Returns an array of SupportGroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SupportGroup
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
