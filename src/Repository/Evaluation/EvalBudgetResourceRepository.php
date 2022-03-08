<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvalBudgetResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvalBudgetResource|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvalBudgetResource|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvalBudgetResource[]    findAll()
 * @method EvalBudgetResource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvalBudgetResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvalBudgetResource::class);
    }
}
