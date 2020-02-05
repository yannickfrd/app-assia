<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\RolePerson;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method RolePerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method RolePerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method RolePerson[]    findAll()
 * @method RolePerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RolePersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RolePerson::class);
    }
}
