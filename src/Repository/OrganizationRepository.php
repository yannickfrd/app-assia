<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Organization;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Organization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organization[]    findAll()
 * @method Organization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    /**
     * Retourne tous les dispositifs
     * @return Query
     */
    public function findAllOrganizationsQuery(): Query
    {
        $query =  $this->createQueryBuilder("o")
            ->select("o");

        return $query->orderBy("o.name", "ASC")
            ->getQuery();
    }
}
