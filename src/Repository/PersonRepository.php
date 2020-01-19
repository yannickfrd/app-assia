<?php

namespace App\Repository;

use App\Entity\Person;

use Doctrine\ORM\Query;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
     * Retourne toutes les personnes
     * 
     * @return Query
     */
    public function findAllPeopleQuery($personSearch, $search = null): Query
    {
        $query =  $this->createQueryBuilder("p")
            ->select("p");
        if ($search) {
            $query->Where("CONCAT(p.lastname,' ' ,p.firstname) LIKE :search")
                ->setParameter("search", '%' . $search . '%');
        }
        if ($personSearch->getFirstname()) {
            $query->andWhere("p.firstname LIKE :firstname")
                ->setParameter("firstname", $personSearch->getFirstname() . '%');
        }
        if ($personSearch->getLastname()) {
            $query->andWhere("p.lastname LIKE :lastname")
                ->setParameter("lastname", $personSearch->getLastname() . '%');
        }

        if ($personSearch->getBirthdate()) {
            $query->andWhere("p.birthdate = :birthdate")
                ->setParameter("birthdate", $personSearch->getBirthdate());
        }
        if ($personSearch->getGender()) {
            $query->andWhere("p.gender = :gender")
                ->setParameter("gender", $personSearch->getGender());
        }
        if ($personSearch->getPhone()) {
            $query->andWhere("p.phone1 = :phone OR p.phone2 = :phone")
                ->setParameter("phone", $personSearch->getPhone());
        }
        return $query->orderBy("p.lastname", "ASC")
            ->getQuery();
    }

    /**
     * Trouve toutes les personnes à exporter
     *
     */
    public function findPeopleToExport($personSearch)
    {
        $query = $this->findAllPeopleQuery($personSearch);
        return $query->getResult();
    }

    /**
     *  Trouve toutes les personnes
     */
    public function findPeopleByResearch($search)
    {
        return $this->createQueryBuilder("p")
            ->select("p")
            ->Where("CONCAT(p.lastname,' ' ,p.firstname) LIKE :search OR CONCAT(p.firstname,' ' ,p.lastname) LIKE :search")
            ->setParameter("search", '%' . $search . '%')
            ->orderBy("p.lastname, p.firstname", "ASC")
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
