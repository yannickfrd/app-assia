<?php

namespace App\Repository;

use App\Entity\Export;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Export|null find($id, $lockMode = null, $lockVersion = null)
 * @method Export|null findOneBy(array $criteria, array $orderBy = null)
 * @method Export[]    findAll()
 * @method Export[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExportRepository extends ServiceEntityRepository
{
    protected $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, Export::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Return all exports.
     */
    public function findExportsQuery(): Query
    {
        return $this->createQueryBuilder('e')->select('e')
            ->leftJoin('e.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')

            ->andWhere('e.createdBy = :user')
            ->setParameter('user', $this->currentUser->getUser())

            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }
}
