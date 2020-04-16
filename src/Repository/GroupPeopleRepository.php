<?php

namespace App\Repository;

use App\Entity\GroupPeople;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

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
     * Donne le groupe de personnes.
     */
    public function findGroupPeopleById(int $id): ?GroupPeople
    {
        return $this->createQueryBuilder('g')
            ->select('g')
            ->leftJoin('g.createdBy', 'createdBy')->addselect('PARTIAL createdBy.{id, firstname, lastname}')
            ->leftJoin('g.updatedBy', 'updatedBy')->addselect('PARTIAL updatedBy.{id, firstname, lastname}')
            ->leftJoin('g.rolePerson', 'r')->addselect('PARTIAL r.{id, role, head}')
            ->leftJoin('r.person', 'p')->addselect('p')
            ->leftJoin('g.supports', 'sg')->addselect('PARTIAL sg.{id, status, startDate, endDate, updatedAt}')
            ->leftJoin('sg.referent', 'ref')->addselect('PARTIAL ref.{id, firstname, lastname, email, phone1}')
            ->leftJoin('sg.service', 's')->addselect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.device', 'd')->addselect('PARTIAL d.{id, name}')
            ->leftJoin('s.pole', 'pole')->addselect('PARTIAL pole.{id, name}')
            ->leftJoin('g.referents', 'referents')->addselect('referents')

            ->andWhere('g.id = :id')
            ->setParameter('id', $id)

            ->orderBy('p.birthdate', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne tous les groupes de personnes.
     */
    public function findAllGroupPeopleQuery($groupPeopleSearch): Query
    {
        $query = $this->createQueryBuilder('g')
            ->select('PARTIAL g.{id, familyTypology, nbPeople}')

            ->innerJoin('g.rolePerson', 'r')->addselect('PARTIAL r.{id, role, head}')
            ->innerJoin('r.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')

            ->andWhere('r.head = TRUE');

        if ($groupPeopleSearch->getFirstname()) {
            $query->andWhere('p.firstname LIKE :firstname')
                ->setParameter('firstname', $groupPeopleSearch->getFirstname().'%');
        }
        if ($groupPeopleSearch->getLastname()) {
            $query->andWhere('p.lastname LIKE :lastname')
                ->setParameter('lastname', $groupPeopleSearch->getLastname().'%');
        }
        if ($groupPeopleSearch->getBirthdate()) {
            $query->andWhere('p.birthdate = :birthdate')
                ->setParameter('birthdate', $groupPeopleSearch->getBirthdate());
        }
        if ($groupPeopleSearch->getHead()) {
            $query->andWhere('r.head = :head')
                ->setParameter('head', $groupPeopleSearch->getHead());
        }
        if ($groupPeopleSearch->getFamilyTypology()) {
            $query->andWhere('g.familyTypology = :familyTypology')
                ->setParameter('familyTypology', $groupPeopleSearch->getFamilyTypology());
        }
        if ($groupPeopleSearch->getNbPeople()) {
            $query->andWhere('g.nbPeople = :nbPeople')
                ->setParameter('nbPeople', $groupPeopleSearch->getNbPeople());
        }

        return $query->orderBy('g.id', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    public function countAllGroups(array $criteria = null)
    {
        $query = $this->createQueryBuilder('g')->select('COUNT(g.id)');

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
