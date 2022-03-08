<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvalBudgetCharge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvalBudgetCharge|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvalBudgetCharge|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvalBudgetCharge[]    findAll()
 * @method EvalBudgetCharge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvalBudgetChargeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvalBudgetCharge::class);
    }
}
