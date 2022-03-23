<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Indicator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Indicator|null find($id, $lockMode = null, $lockVersion = null)
 * @method Indicator|null findOneBy(array $criteria, array $orderBy = null)
 * @method Indicator[]    findAll()
 * @method Indicator[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndicatorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Indicator::class);
    }

    public function findIndicatorsQuery(): Query
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.date', 'DESC')
            ->getQuery();
    }
}
