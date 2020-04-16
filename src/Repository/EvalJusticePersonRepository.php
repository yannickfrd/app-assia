<?php

namespace App\Repository;

use App\Entity\EvalJusticePerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvalJusticePerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvalJusticePerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvalJusticePerson[]    findAll()
 * @method EvalJusticePerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvalJusticePersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvalJusticePerson::class);
    }
}
