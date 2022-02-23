<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvalBudgetDebt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvalBudgetDebt|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvalBudgetDebt|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvalBudgetDebt[]    findAll()
 * @method EvalBudgetDebt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvalBudgetDebtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvalBudgetDebt::class);
    }
}
