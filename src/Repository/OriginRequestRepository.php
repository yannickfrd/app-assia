<?php

namespace App\Repository;

use App\Entity\OriginRequest;
use App\Entity\SupportGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OriginRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method OriginRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method OriginRequest[]    findAll()
 * @method OriginRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OriginRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OriginRequest::class);
    }

    /**
     * Donne l'origine de la demande du suivi social.
     */
    public function findOriginRequest(SupportGroup $supportGroup): ?OriginRequest
    {
        return $this->createQueryBuilder('o')
            ->select('o')
            ->leftJoin('o.supportGroup', 'sg')->addselect('PARTIAL sg.{id, updatedAt, updatedBy}')
            ->leftJoin('o.organization', 'organization')->addselect('PARTIAL organization.{id, name}')

            ->where('o.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
