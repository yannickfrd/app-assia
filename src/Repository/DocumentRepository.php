<?php

namespace App\Repository;

use App\Entity\Document;
use Doctrine\ORM\Query;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
     * Return all documents of group support
     * 
     * @return Query
     */
    public function findAllDocumentsQuery($supportGroupId, $documentSearch): Query
    {
        $query =  $this->createQueryBuilder("d")
            ->andWhere("d.supportGroup = :supportGroup")
            ->setParameter("supportGroup", $supportGroupId);

        if ($documentSearch->getName()) {
            $query->andWhere("d.name LIKE :name")
                ->setParameter("name", '%' . $documentSearch->getName() . '%');
        }
        if ($documentSearch->getType()) {
            $query->andWhere("d.type = :type")
                ->setParameter("type", $documentSearch->getType());
        }
        $query = $query->orderBy("d.createdAt", "DESC");
        return $query->getQuery();
    }
}
