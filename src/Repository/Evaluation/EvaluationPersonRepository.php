<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvaluationPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvaluationPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationPerson[]    findAll()
 * @method EvaluationPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationPersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationPerson::class);
    }
}
