<?php

namespace App\Repository;

use App\Entity\Referent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Referent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Referent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Referent[]    findAll()
 * @method Referent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referent::class);
    }
}
