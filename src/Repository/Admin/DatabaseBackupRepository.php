<?php

namespace App\Repository\Admin;

use App\Entity\Admin\DatabaseBackup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DatabaseBackup|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatabaseBackup|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatabaseBackup[]    findAll()
 * @method DatabaseBackup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatabaseBackupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatabaseBackup::class);
    }

    /**
     * Return all database backups.
     */
    public function findBackupsQuery(): Query
    {
        return $this->createQueryBuilder('b')->select('b')
            ->leftJoin('b.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')

            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
        ;
    }
}
