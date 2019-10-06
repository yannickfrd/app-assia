<?php

namespace App\Repository;

use App\Entity\GroupPeople;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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

    
    // /**
    //  * @return GroupPeople[] Returns an array of GroupPeople objects
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
    public function findOneBySomeField($value): ?GroupPeople
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
    public function findPeopleFromGroup($groupPeople) {
        
        return $this->createQueryBuilder("grp")
                    ->leftJoin("grp.rolePerson", "role")
                    ->leftJoin("role.person", "pers")
                    ->select("grp", "role", "pers")
                    ->andWhere("grp = :grp")
                    ->setParameter("grp", $groupPeople)
                    ->getQuery()
                    ->getResult();
    }

        // Trouve tous les personnes du même groupe ménage
        public function findPeopleFromGroupV2($groupPeople) {
        
            $q = Doctrine_Query::create()
            ->from('GroupPeople g')
            ->leftJoin('g.rolePerson r')
            ->leftJoin('r.person p')
            ->where('g.id = ?', 1);
          $user = $q->fetchOne();
        }
    



}
