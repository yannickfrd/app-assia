<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Export;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Export|null find($id, $lockMode = null, $lockVersion = null)
 * @method Export|null findOneBy(array $criteria, array $orderBy = null)
 * @method Export[]    findAll()
 * @method Export[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExportRepository extends ServiceEntityRepository
{
    protected $user;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Export::class);

        $this->user = $security->getUser();
    }

    /**
     * Return all exports.
     */
    public function findExportsQuery(): Query
    {
        return $this->createQueryBuilder('e')->select('e')
            ->leftJoin('e.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')

            ->andWhere('e.createdBy = :user')
            ->setParameter('user', $this->user)

            ->orderBy('e.createdAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }
}
