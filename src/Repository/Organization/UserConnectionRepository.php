<?php

namespace App\Repository\Organization;

use App\Entity\Organization\UserConnection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserConnection|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserConnection|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserConnection[]    findAll()
 * @method UserConnection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserConnection::class);
    }

    /**
     * Compte le nombre de connections.
     */
    public function countConnections(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('c')->select('COUNT(c.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('startDate' === $key) {
                    $qb->andWhere('c.connectionAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere('c.connectionAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }
}
