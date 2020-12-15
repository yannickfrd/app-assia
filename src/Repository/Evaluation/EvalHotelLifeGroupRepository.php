<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvalHotelLifeGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvalHotelLifeGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvalHotelLifeGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvalHotelLifeGroup[]    findAll()
 * @method EvalHotelLifeGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvalHotelLifeGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvalHotelLifeGroup::class);
    }
}
