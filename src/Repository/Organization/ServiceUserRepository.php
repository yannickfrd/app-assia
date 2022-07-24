<?php

namespace App\Repository\Organization;

use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServiceUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceUser[]    findAll()
 * @method ServiceUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceUser::class);
    }

    public function findUserServices(User $user)
    {
        return $this->createQueryBuilder('su')
            ->leftJoin('su.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id, name}')

            ->andWhere('su.user = :user')
            ->setParameter('user', $user)

            ->orderBy('s.name', 'ASC')

            ->getQuery()
            ->getResult()
        ;
    }
}
