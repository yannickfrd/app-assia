<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Document;
use App\Entity\SupportGroup;
use App\Form\Model\DocumentSearch;
use Doctrine\Persistence\ManagerRegistry;
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
     * @param int $supportGroupId
     * @param DocumentSearch $documentSearch
     * @return Query
     */
    public function findAllDocumentsQuery($supportGroupId, DocumentSearch $documentSearch): Query
    {
        $query =  $this->createQueryBuilder("d")
            ->andWhere("d.supportGroup = :supportGroup")
            ->setParameter("supportGroup", $supportGroupId);

        if ($documentSearch->getName()) {
            $query->andWhere("d.name LIKE :name OR d.content LIKE :name")
                ->setParameter("name", '%' . $documentSearch->getName() . '%');
        }
        if ($documentSearch->getType()) {
            $query->andWhere("d.type = :type")
                ->setParameter("type", $documentSearch->getType());
        }
        $query = $query->orderBy("d.createdAt", "DESC");
        return $query->getQuery();
    }

    public function countAllDocuments(array $criteria = null)
    {
        $query = $this->createQueryBuilder("d")->select("COUNT(d.id)");

        if ($criteria) {

            // $query = $query->leftJoin("d.supportGroup", "sg")->addselect("PARTIAL sg.{id, referent, status, service, device}");

            foreach ($criteria as $key => $value) {
                if ($key == "user") {
                    $query = $query->andWhere("d.createdBy = :user")
                        ->setParameter("user", $value);
                }
                if ($key == "status") {
                    $query = $query->andWhere("sg.status = :status")
                        ->setParameter("status", $value);
                }
                if ($key == "service") {
                    $query = $query->andWhere("sg.service = :service")
                        ->setParameter("service", $value);
                }
                if ($key == "device") {
                    $query = $query->andWhere("sg.device = :device")
                        ->setParameter("device", $value);
                }
            }
        }
        return $query->getQuery()
            ->getSingleScalarResult();
    }

    public function sumSizeAllDocuments(array $criteria = null)
    {
        $query = $this->createQueryBuilder("d")->select("SUM(d.size)");

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
