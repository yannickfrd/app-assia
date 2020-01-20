<?php

namespace App\Repository;

use App\Entity\Note;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Note|null find($id, $lockMode = null, $lockVersion = null)
 * @method Note|null findOneBy(array $criteria, array $orderBy = null)
 * @method Note[]    findAll()
 * @method Note[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Note::class);
    }

    /**
     * Return all notes of group support
     * 
     * @return Query
     */
    public function findAllNotesQuery($supportGroupId, $noteSearch): Query
    {
        $query =  $this->createQueryBuilder("n")
            ->andWhere("n.supportGroup = :supportGroup")
            ->setParameter("supportGroup", $supportGroupId);

        if ($noteSearch->getContent()) {
            $query->andWhere("n.content LIKE :content")
                ->setParameter("content", '%' . $noteSearch->getContent() . '%');
        }
        if ($noteSearch->getStatus()) {
            $query->andWhere("n.status = :status")
                ->setParameter("status", $noteSearch->getStatus());
        }
        if ($noteSearch->getType()) {
            $query->andWhere("n.type = :type")
                ->setParameter("type", $noteSearch->getType());
        }
        $query = $query->orderBy("n.createdAt", "DESC");
        return $query->getQuery();
    }
}
