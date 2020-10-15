<?php

namespace App\Repository;

use App\Entity\UserConnection;
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
     *
     * @return mixed
     */
    public function countConnections(array $criteria = null)
    {
        $query = $this->createQueryBuilder('c')->select('COUNT(c.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('startDate' == $key) {
                    $query = $query->andWhere('c.connectionAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' == $key) {
                    $query = $query->andWhere('c.connectionAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
