<?php

namespace App\Repository\People;

use App\Entity\People\Person;
use App\Form\Model\People\DuplicatedPeopleSearch;
use App\Form\Model\People\PersonSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * Donne le groupe de personnes.
     */
    public function findPersonById(int $id): ?Person
    {
        return $this->createQueryBuilder('p')->select('p')
            ->leftJoin('p.createdBy', 'createdBy')->addSelect('PARTIAL createdBy.{id, firstname, lastname, email, phone1}')
            ->leftJoin('p.updatedBy', 'updatedBy')->addSelect('PARTIAL updatedBy.{id, firstname, lastname, email, phone1}')
            ->leftJoin('p.rolesPerson', 'r')->addSelect('PARTIAL r.{id, role, head}')
            ->leftJoin('r.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople, createdAt, updatedAt}')

            ->andWhere('p.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Retourne toutes les personnes.
     */
    public function findPeopleQuery(PersonSearch $personSearch, string $searchQuery = null, int $maxResult = 20): Query
    {
        $query = $this->createQueryBuilder('p')->select('p');

        if ($searchQuery) {
            $query->where("CONCAT(p.lastname,' ' , p.firstname) LIKE :search")
                ->setParameter('search', '%'.$searchQuery.'%');
        }

        if ($personSearch->getSiSiaoId()) {
            $query->leftJoin('p.rolesPerson', 'r')->addSelect('PARTIAL r.{id}')
                ->leftJoin('r.peopleGroup', 'g')->addSelect('PARTIAL g.{id, siSiaoId}')

                ->andWhere('g.siSiaoId = :siSiaoId')
                ->setParameter('siSiaoId', $personSearch->getSiSiaoId());
        }
        if ($personSearch->getFirstname()) {
            $query->andWhere('p.firstname LIKE :firstname')
                ->setParameter('firstname', $personSearch->getFirstname().'%');
        }
        if ($personSearch->getLastname()) {
            $query->andWhere('p.lastname LIKE :lastname')
                ->setParameter('lastname', $personSearch->getLastname().'%');
        }

        if ($personSearch->getBirthdate()) {
            $query->andWhere('p.birthdate = :birthdate')
                ->setParameter('birthdate', $personSearch->getBirthdate());
        }

        if ($maxResult) {
            $query->setMaxResults($maxResult);
        }

        return $query->addOrderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->getQuery();
    }

    /**
     * Trouve toutes les personnes à exporter.
     *
     * @return Person[]|null
     */
    public function findPeopleToExport(PersonSearch $personSearch): ?array
    {
        $query = $this->findPeopleQuery($personSearch);

        return $query->getResult();
    }

    /**
     *  Recherche une personne par son nom, prénom ou date de naissance.
     *
     * @return Person[]|null
     */
    public function findPeopleByResearch(string $search = null): ?array
    {
        $query = $this->createQueryBuilder('p')->select('p');

        $date = \DateTime::createFromFormat('d-m-Y', $search) ?? false;

        if ($date) {
            $query->where('p.birthdate = :birthdate')
                ->setParameter('birthdate', $date->format('Y-m-d'));
        } else {
            $query->where("CONCAT(p.lastname,' ' , p.firstname) LIKE :search OR CONCAT(p.firstname,' ' , p.lastname) LIKE :search")
                ->setParameter('search', '%'.$search.'%');
        }

        return $query
            ->orderBy('p.lastname, p.firstname', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de personnes.
     */
    public function countPeople(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('p')->select('COUNT(p.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('startDate' === $key) {
                    $query = $query->andWhere('p.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere('p.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $query = $query->andWhere('p.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Person[]|null
     */
    public function findDuplicatedPeople(DuplicatedPeopleSearch $search): ?array
    {
        $query = $this->createQueryBuilder('p')->select('p');

        if ($search->getLastname()) {
            $query->addGroupBy('p.lastname');
        }
        if ($search->getFirstname()) {
            $query->addGroupBy('p.firstname');
        }
        if ($search->getBirthdate()) {
            $query->addGroupBy('p.birthdate');
        }

        return $query->having('COUNT(p.id) > 1')

            ->getQuery()
            ->getResult();
    }

    public function findOnePersonByFirstname(string $firstname = null, bool $genderIsNotNull = true): ?Person
    {
        $query = $this->createQueryBuilder('p')->select('p');

        if ($firstname) {
            $query->andWhere('p.firstname = :firstname')
            ->setParameter('firstname', $firstname);
        }
        if ($genderIsNotNull) {
            $query->andWhere('p.gender != :gender')
            ->setParameter('gender', 99);
        }

        return $query->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
