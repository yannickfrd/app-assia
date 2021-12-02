<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Organization;
use App\Entity\Organization\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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
    public function findOrganizationsQuery(): Query
    {
        $query = $this->createQueryBuilder('o')->select('o');

        return $query->orderBy('o.name', 'ASC')
            ->getQuery();
    }

    /**
     * Retourne les organismes prescripteurs rattachés au service.
     */
    public function getOrganizationsQueryBuilder(Service $service = null): QueryBuilder
    {
        $query = $this->createQueryBuilder('o')->select('PARTIAL o.{id, name}');

        if ($service && $this->countOrganizationsInService($service)) {
            $query = $query->leftJoin('o.services', 's')->addSelect('PARTIAL s.{id, name}')
                ->where('s.id = :service')
                ->setParameter('service', $service);
        }

        return $query->orderBy('o.name', 'ASC');
    }

    /**
     * Compte le nombre d'organismes prescripteurs rattachés au service.
     */
    public function countOrganizationsInService(Service $service): ?int
    {
        return $this->createQueryBuilder('o')->select('o')
            ->join('o.services', 's')->select('COUNT(s.id)')

            ->where('s.id = :service')
            ->setParameter('service', $service)

            ->getQuery()
            ->getSingleScalarResult();
    }
}
