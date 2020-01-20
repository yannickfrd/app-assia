<?php

namespace App\Repository;

use App\Entity\Pole;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Pole|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pole|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pole[]    findAll()
 * @method Pole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pole::class);
    }

    /**
     * Retourne toutes les personnes
     * @return Query
     */
    public function findAllPolesQuery(): Query
    {
        $query =  $this->createQueryBuilder("p")
            ->select("p");
        return $query->orderBy("p.name", "ASC")
            ->getQuery();
    }
}
