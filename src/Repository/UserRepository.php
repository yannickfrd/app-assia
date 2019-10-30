<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Query;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retourne toutes les personnes
     * @return Query
     */
    public function findAllUsersQuery($userSearch): Query
    {
        $query =  $this->createQueryBuilder("u");
        $query = $query->select("u")
            ->leftJoin("u.roleUser", "r")
            ->leftJoin("r.department", "d")
            ->leftJoin("d.pole", "p")
            ->addselect("r")
            ->addselect("d")
            ->addselect("p");
        if ($userSearch->getPole()) {
            $query->andWhere("p.id = :pole_id")
                ->setParameter("pole_id", $userSearch->getPole());
        }
        if ($userSearch->getFirstname()) {
            $query->andWhere("u.firstname LIKE :firstname")
                ->setParameter("firstname", $userSearch->getFirstname() . '%');
        }
        if ($userSearch->getLastname()) {
            $query->andWhere("u.lastname LIKE :lastname")
                ->setParameter("lastname", $userSearch->getLastname() . '%');
        }
        if ($userSearch->getPhone()) {
            $query->andWhere("u.phone = :phone")
                ->setParameter("phone", $userSearch->getPhone());
        }
        // if ($userSearch->getDepartment()) {
        //     foreach ($userSearch->getDepartment() as $key => $department) {
        //         $query->orWhere("d.id = :department_$key")
        //             ->setParameter("department_$key", $department);
        //     }
        // }
        if ($userSearch->getDepartment()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($userSearch->getDepartment() as $department) {
                $orX->add($expr->eq("d.id", $department));
            }
            $query->andWhere($orX);
        }
        $query = $query->orderBy("u.lastname", "ASC");
        return $query->getQuery();
    }



    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
