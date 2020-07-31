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
            ->leftJoin('g.rolePeople', 'r')->addselect('PARTIAL r.{id, role, head}')
            ->leftJoin('r.person', 'p')->addselect('p')
            ->leftJoin('g.supports', 'sg')->addselect('sg')
            ->leftJoin('sg.referent', 'ref')->addselect('PARTIAL ref.{id, firstname, lastname, email, phone1}')
            ->leftJoin('sg.service', 's')->addselect('s')
            ->leftJoin('sg.device', 'd')->addselect('d')
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
    public function findAllGroupPeopleQuery($search): Query
    {
        $query = $this->createQueryBuilder('g')
            ->select('PARTIAL g.{id, familyTypology, nbPeople}')

            ->innerJoin('g.rolePeople', 'r')->addselect('PARTIAL r.{id, role, head}')
            ->innerJoin('r.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')

            ->andWhere('r.head = TRUE');

        if ($search->getFirstname()) {
            $query->andWhere('p.firstname LIKE :firstname')
                ->setParameter('firstname', $search->getFirstname().'%');
        }
        if ($search->getLastname()) {
            $query->andWhere('p.lastname LIKE :lastname')
                ->setParameter('lastname', $search->getLastname().'%');
        }
        if ($search->getBirthdate()) {
            $query->andWhere('p.birthdate = :birthdate')
                ->setParameter('birthdate', $search->getBirthdate());
        }
        if ($search->getHead()) {
            $query->andWhere('r.head = :head')
                ->setParameter('head', $search->getHead());
        }
        if ($search->getFamilyTypology()) {
            $query->andWhere('g.familyTypology = :familyTypology')
                ->setParameter('familyTypology', $search->getFamilyTypology());
        }
        if ($search->getNbPeople()) {
            $query->andWhere('g.nbPeople = :nbPeople')
                ->setParameter('nbPeople', $search->getNbPeople());
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
