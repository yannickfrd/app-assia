<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvalBudgetPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvalBudgetPersonRepository|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvalBudgetPersonRepository|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvalBudgetPersonRepository[]    findAll()
 * @method EvalBudgetPersonRepository[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvalBudgetPersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvalBudgetPerson::class);
    }
}
