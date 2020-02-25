<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\SupportGroup;
use App\Entity\OriginRequest;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
     * Donne l'origine de la demande du suivi social
     *
     * @param SupportGroup $supportGroup
     * @return OriginRequest|null
     */
    public function findOriginRequest(SupportGroup $supportGroup): ?OriginRequest
    {
        return $this->createQueryBuilder("o")
            ->select("o")
            ->leftJoin("o.supportGroup", "sg")->addselect("PARTIAL sg.{id, updatedAt, updatedBy}")

            ->where("o.supportGroup = :supportGroup")
            ->setParameter("supportGroup", $supportGroup)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
