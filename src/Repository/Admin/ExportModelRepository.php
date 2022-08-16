<?php

namespace App\Repository\Admin;

use App\Entity\Admin\ExportModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExportModel|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExportModel|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExportModel[]    findAll()
 * @method ExportModel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExportModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExportModel::class);
    }

    public function add(ExportModel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ExportModel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllExportModelsQuery(): Query
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.createdBy', 'u1')->addSelect('PARTIAL u1.{id, firstname, lastname}')
            ->leftJoin('e.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')
            ->orderBy('e.title', 'ASC')
            ->getQuery()
        ;
    }
}
