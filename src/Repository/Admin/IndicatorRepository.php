<?php

namespace App\Repository\Admin;

use Doctrine\ORM\Query;
use App\Entity\Admin\Indicator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
