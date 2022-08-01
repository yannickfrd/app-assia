<?php

namespace App\Repository\People;

use App\Entity\People\Person;
use App\Form\Model\People\DuplicatedPeopleSearch;
use App\Form\Model\People\PersonSearch;
use App\Repository\Traits\QueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Person|null find($id, $lockMode = null, $lockVersion = null)
 * @method Person|null findOneBy(array $criteria, array $orderBy = null)
 * @method Person[]    findAll()
 * @method Person[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonRepository extends ServiceEntityRepository
{
    use QueryTrait;

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

            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Retourne toutes les personnes.
     */
    public function findPeopleQuery(PersonSearch $personSearch, ?string $searchQuery = null, int $maxResult = 20): Query
    {
        $qb = $this->createQueryBuilder('p')->select('p');

        $qb = $this->filtersSearchQuery($qb, $searchQuery);

        if ($personSearch->getSiSiaoId()) {
            $qb->leftJoin('p.rolesPerson', 'r')->addSelect('PARTIAL r.{id}')
                ->leftJoin('r.peopleGroup', 'g')->addSelect('PARTIAL g.{id, siSiaoId}')

                ->andWhere('g.siSiaoId = :siSiaoId')
                ->setParameter('siSiaoId', $personSearch->getSiSiaoId());
        }
        if ($personSearch->getFirstname()) {
            $qb->andWhere('p.firstname LIKE :firstname')
                ->setParameter('firstname', $personSearch->getFirstname().'%');
        }
        if ($personSearch->getLastname()) {
            $qb->andWhere('p.lastname LIKE :lastname')
                ->setParameter('lastname', $personSearch->getLastname().'%');
        }

        if ($personSearch->getBirthdate()) {
            $qb->andWhere('p.birthdate = :birthdate')
                ->setParameter('birthdate', $personSearch->getBirthdate());
        }

        if ($maxResult) {
            $qb->setMaxResults($maxResult);
        }

        return $qb
            ->addOrderBy('p.lastname', 'ASC')
            ->addOrderBy('p.firstname', 'ASC')
            ->getQuery()
        ;
    }

    /**
     * Trouve toutes les personnes à exporter.
     *
     * @return Person[]
     */
    public function findPeopleToExport(PersonSearch $personSearch): array
    {
        return $this->findPeopleQuery($personSearch)
            ->getResult()
        ;
    }

    /**
     *  Recherche une personne par son nom, prénom ou date de naissance.
     *
     * @return Person[]
     */
    public function findPeopleByResearch(?string $searchQuery = null): array
    {
        $qb = $this->createQueryBuilder('p');

        return $this->filtersSearchQuery($qb, $searchQuery)
            ->orderBy('p.lastname, p.firstname', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Compte le nombre de personnes.
     */
    public function countPeople(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('p')->select('COUNT(p.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('startDate' === $key) {
                    $qb->andWhere('p.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere('p.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $qb->andWhere('p.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @return Person[]
     */
    public function findDuplicatedPeople(DuplicatedPeopleSearch $search): array
    {
        $qb = $this->createQueryBuilder('p')->select('p');

        if ($search->getLastname()) {
            $qb->addGroupBy('p.lastname');
        }
        if ($search->getFirstname()) {
            $qb->addGroupBy('p.firstname');
        }
        if ($search->getBirthdate()) {
            $qb->addGroupBy('p.birthdate');
        }

        return $qb
            ->having('COUNT(p.id) > 1')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOnePersonByFirstname(string $firstname = null, bool $genderIsNotNull = true): ?Person
    {
        $qb = $this->createQueryBuilder('p')->select('p');

        if ($firstname) {
            $qb->andWhere('p.firstname = :firstname')
            ->setParameter('firstname', $firstname);
        }
        if ($genderIsNotNull) {
            $qb->andWhere('p.gender != :gender')
            ->setParameter('gender', 99);
        }

        return $qb
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Person[]
     */
    public function findPeopleWithoutSupport(\DateTimeInterface $limitDate): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.rolesPerson', 'r')
            ->leftJoin('p.supports', 'sp')
            ->leftJoin('r.peopleGroup', 'g')
            ->leftJoin('g.supports', 'sg')

            ->where('sp.id IS NULL AND g.id IS NULL') // Toutes les personnes sans suivi et sans groupe
            ->orWhere('sp.id IS NULL AND g.id IS NOT NULL AND sg.id IS NULL') // Toutes les personnes sans suivi et dans un groupe sans suivi

            ->andWhere('p.updatedAt <= :limitDate')
            ->setParameter('limitDate', $limitDate)

            ->getQuery()
            ->getResult()
        ;
    }

    private function filtersSearchQuery(QueryBuilder $qb, ?string $searchQuery): QueryBuilder
    {
        if (null === $searchQuery) {
            return $qb;
        }

        $date = \DateTime::createFromFormat('d-m-Y', $searchQuery);

        // If $search is date
        if (false !== $date) {
            $qb->where('p.birthdate = :birthdate')
                ->setParameter('birthdate', $date->format('Y-m-d'));
        // If $search is identifier
        } elseif (ctype_digit($searchQuery)) {
            $qb
                ->leftJoin('p.rolesPerson', 'r')->addSelect('PARTIAL r.{id}')
                ->leftJoin('r.peopleGroup', 'g')->addSelect('PARTIAL g.{id, siSiaoId}')

                ->where('g.siSiaoId = :search')
                ->setParameter('search', $searchQuery)
            ;
        // else $search is string
        } else {
            $qb->where("CONCAT(p.lastname,' ' , p.firstname) LIKE :search OR CONCAT(p.firstname,' ' , p.lastname) LIKE :search")
                ->setParameter('search', '%'.str_replace('+', ' ', $searchQuery).'%');
        }

        return $qb;
    }
}
