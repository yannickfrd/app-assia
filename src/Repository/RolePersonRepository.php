<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\RolePerson;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method RolePerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method RolePerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method RolePerson[]    findAll()
 * @method RolePerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolePersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RolePerson::class);
    }


    /**
     * @return Query
     */
    // Donne tous les roles de toutes les personnes
    public function findAllRolePeopleQuery($groupPeopleSearch): Query
    {
        $query =  $this->createQueryBuilder("r")
            ->select("r")
            ->leftJoin("r.person", "p")
            ->leftJoin("r.groupPeople", "g")
            ->addselect("p")
            ->addselect("g");
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
        if ($groupPeopleSearch->getHead()) {
            $query->andWhere("r.head = :head")
                ->setParameter("head", $groupPeopleSearch->getHead());
        }
        if ($groupPeopleSearch->getFamilyTypology()) {
            $query->andWhere("g.familyTypology = :familyTypology")
                ->setParameter("familyTypology", $groupPeopleSearch->getFamilyTypology());
        }
        if ($groupPeopleSearch->getNbPeople()) {
            $query->andWhere("g.nbPeople = :nbPeople")
                ->setParameter("nbPeople", $groupPeopleSearch->getNbPeople());
        }
        return $query->orderBy("g.id", "ASC")
            ->getQuery();
    }
}
