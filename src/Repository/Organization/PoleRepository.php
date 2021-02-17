<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Pole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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
     * Retourne toutes les pôles.
     */
    public function findPolesQuery(): Query
    {
        $query = $this->createQueryBuilder('p')->select('p');

        return $query->orderBy('p.name', 'ASC')
            ->getQuery();
    }

    /**
     * Donne la liste des pôles.
     */
    public function getPoleQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('p')->select('PARTIAL p.{id, name}')
            ->where('p.disabledAt IS NULL')
            ->orderBy('p.name', 'ASC');
    }
}
