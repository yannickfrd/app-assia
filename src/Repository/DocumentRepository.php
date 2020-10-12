<?php

namespace App\Repository;

use App\Entity\Document;
use App\Form\Model\DocumentSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Return all documents of group support.
     */
    public function findAllDocumentsQuery(int $supportGroupId, DocumentSearch $search): Query
    {
        $query = $this->createQueryBuilder('d')
            ->andWhere('d.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getName()) {
            $query->andWhere('d.name LIKE :name OR d.content LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getType()) {
            $query->andWhere('d.type = :type')
                ->setParameter('type', $search->getType());
        }
        $query = $query->orderBy('d.createdAt', 'DESC');

        return $query->getQuery();
    }

    public function countDocuments(array $criteria = null)
    {
        $query = $this->createQueryBuilder('d')->select('COUNT(d.id)');

        if ($criteria) {
            $query = $query->leftJoin('d.supportGroup', 'sg');

            foreach ($criteria as $key => $value) {
                if ('service' == $key) {
                    $query = $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' == $key) {
                    $query = $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' == $key) {
                    $query = $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('startDate' == $key) {
                    $query = $query->andWhere('d.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' == $key) {
                    $query = $query->andWhere('d.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
                if ('createdBy' == $key) {
                    $query = $query->andWhere('d.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
                if ('status' == $key) {
                    $query = $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    public function sumSizeAllDocuments(array $criteria = null)
    {
        $query = $this->createQueryBuilder('d')->select('SUM(d.size)');

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
