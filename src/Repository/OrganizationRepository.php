<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Organization;
use Doctrine\ORM\QueryBuilder;
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
     * Retourne tous les organismes prescripteurs.
     */
    public function findAllOrganizationsQuery(): Query
    {
        $query = $this->createQueryBuilder('o')->select('o');

        return $query->orderBy('o.name', 'ASC')
            ->getQuery();
    }

    /**
     * Retourne les organismes prescripteurs rattachés au service.
     */
    public function getOrganizationsQueryList(int $serviceId): QueryBuilder
    {
        $query = $this->createQueryBuilder('o')->select('PARTIAL o.{id, name}');

        if ($this->countOrganizationsInService($serviceId)) {
            $query = $query->leftJoin('o.services', 's')->addSelect('PARTIAL s.{id}')
                ->where('s.id = :service')
                ->setParameter('service', $serviceId);
        }

        return $query->orderBy('o.name', 'ASC');
    }

    /**
     * Compte le nombre d'organismes prescripteurs rattachés au service.
     */
    public function countOrganizationsInService(int $serviceId)
    {
        return $this->createQueryBuilder('o')->select('o')
            ->join('o.services', 's')->select('COUNT(s.id)')

            ->where('s.id = :service')
            ->setParameter('service', $serviceId)

            ->getQuery()
            ->getSingleScalarResult();
    }
}
