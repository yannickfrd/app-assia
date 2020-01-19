<?php

namespace App\Repository;

use App\Entity\ServiceUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

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
}
