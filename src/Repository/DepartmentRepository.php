<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Department;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Department|null find($id, $lockMode = null, $lockVersion = null)
 * @method Department|null findOneBy(array $criteria, array $orderBy = null)
 * @method Department[]    findAll()
 * @method Department[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepartmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Department::class);
    }

    /**
     * Retourne toutes les personnes
     * @return Query
     */
    public function findAllDepartmentsQuery($departmentSearch): Query
    {
        $query =  $this->createQueryBuilder("d")
            ->select("d")
            ->leftJoin("d.pole", "p")
            ->addselect("p");
        if ($departmentSearch->getName()) {
            $query->andWhere("d.name LIKE :name")
                ->setParameter("name", $departmentSearch->getName() . '%');
        }
        if ($departmentSearch->getPhone()) {
            $query->andWhere("d.phone = :phone")
                ->setParameter("phone", $departmentSearch->getPhone());
        }
        if ($departmentSearch->getPole()) {
            $query = $query->andWhere("p.id = :pole_id")
                ->setParameter("pole_id", $departmentSearch->getPole());
        }
        return $query->orderBy("d.name", "ASC")
            ->getQuery();
    }


    // /**
    //  * @return Department[] Returns an array of Department objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Department
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
