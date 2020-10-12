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
            ->leftJoin('g.createdBy', 'createdBy')->addSelect('PARTIAL createdBy.{id, firstname, lastname}')
            ->leftJoin('g.updatedBy', 'updatedBy')->addSelect('PARTIAL updatedBy.{id, firstname, lastname}')
            ->leftJoin('g.rolePeople', 'r')->addSelect('PARTIAL r.{id, role, head}')
            ->leftJoin('r.person', 'p')->addSelect('p')
            ->leftJoin('g.supports', 'sg')->addSelect('sg')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname, email, phone1}')
            ->leftJoin('sg.service', 's')->addSelect('s')
            ->leftJoin('sg.device', 'd')->addSelect('d')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('g.referents', 'referents')->addSelect('referents')

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

            ->innerJoin('g.rolePeople', 'r')->addSelect('PARTIAL r.{id, role, head}')
            ->innerJoin('r.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')

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

    public function countGroups(array $criteria = null)
    {
        $query = $this->createQueryBuilder('g')->select('COUNT(g.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('startDate' == $key) {
                    $query = $query->andWhere('g.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' == $key) {
                    $query = $query->andWhere('g.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
                if ('createdBy' == $key) {
                    $query = $query->andWhere('g.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
