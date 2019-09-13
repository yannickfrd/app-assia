<?php

namespace App\Repository;

use App\Entity\PeopleGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method PeopleGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeopleGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeopleGroup[]    findAll()
 * @method PeopleGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeopleGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PeopleGroup::class);
    }

    // /**
    //  * @return PeopleGroup[] Returns an array of PeopleGroup objects
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
    public function findOneBySomeField($value): ?PeopleGroup
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
        // Trouve tous les personnes du même groupe ménage
    public function findPeopleFromGroup($peopleGroup) {
        
        return $this->createQueryBuilder("grp")
                    ->leftJoin("grp.rolePeople", "role")
                    ->leftJoin("role.person", "pers")
                    ->select("grp", "role", "pers")
                    ->andWhere("grp = :grp")
                    ->setParameter("grp", $peopleGroup)
                    ->getQuery()
                    ->getResult();
    }

        // Trouve tous les personnes du même groupe ménage
        public function findPeopleFromGroupV2($peopleGroup) {
        
            $q = Doctrine_Query::create()
            ->from('PeopleGroup g')
            ->leftJoin('g.rolePeople r')
            ->leftJoin('r.person p')
            ->where('g.id = ?', 1);
          $user = $q->fetchOne();
        }
    



}
