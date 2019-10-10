<?php

namespace App\Repository;

use App\Entity\Person;
use Doctrine\ORM\Query;
use App\Entity\GroupPeople;
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

    // /**
    //  * @return Person[] Returns an array of Person objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Person
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    /**
     * @return Query
     */
    // Trouve toutes les personnes
    public function findAllPeopleQuery($personSearch): Query
    {
        $query =  $this->createQueryBuilder("p");
        $query = $query->select("p");
        if ($personSearch->getFirstname()) {
            $query = $query
                ->andWhere("p.firstname LIKE :firstname")
                ->setParameter("firstname", $personSearch->getFirstname() . '%');
        }
        if ($personSearch->getLastname()) {
            $query = $query
                ->andWhere("p.lastname LIKE :lastname")
                ->setParameter("lastname", $personSearch->getLastname() . '%');
            }
      
        if ($personSearch->getBirthdate()) {
            $query = $query
                ->andWhere("p.birthdate = :birthdate")
                ->setParameter("birthdate", $personSearch->getBirthdate());
        }
        if ($personSearch->getGender()) {
            $query = $query
                ->andWhere("p.gender = :gender")
                ->setParameter("gender", $personSearch->getGender());
        }
        if ($personSearch->getPhone()) {
            $query = $query
                ->andWhere("p.phone1 = :phone OR p.phone2 = :phone")
                ->setParameter("phone", $personSearch->getPhone());
        }
        $query = $query->orderBy("p.lastname", "ASC");
        return $query->getQuery();
    }
        
    // Trouve tous les personnes du même groupe ménage
    public function findByGroupPeople($groupPeople) {
        
        return $this->createQueryBuilder("p")
                    ->select("p")
                    ->leftJoin("p.groupPeoples", "g")
                    ->addSelect("g")
                    ->andWhere("g = :g")
                    ->setParameter("g", $groupPeople)
                    ->getQuery()
                    ->getResult();
    }
    
    // Trouve tous les personnes du même groupe ménage
    public function Test($groupPeople) {
        
        return $this->createQueryBuilder("p")
                    ->leftJoin("p.rolePerson", "r")
                    ->leftJoin("r.groupPeople", "g")
                    ->select("p")
                    ->addSelect("r")
                    ->addSelect("g")
                    ->andWhere("g = :g")
                    ->setParameter("g", $groupPeople)
                    ->getQuery()
                    ->getResult();
    }


    // Trouve tous les personnes du même groupe ménage (première version)
    // public function findByGroupPeopleV1($groupPeople){
    //     $query = $this->createQueryBuilder('p')
    //                   ->select('p')
    //                   ->leftJoin('p.groupPeoples', 'g')
    //                   ->addSelect('g');
    //     $query = $query->add('where', $query->expr()->in('g', ':g'))
    //                   ->setParameter('g', $groupPeople)
    //                   ->getQuery()
    //                   ->getResult();
          
    //     return $query;
    // }  
}