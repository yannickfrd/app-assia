<?php

namespace App\Repository\Support;

use App\Entity\Organization\User;
use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Model\Support\SupportDocumentSearch;
use App\Repository\Traits\QueryTrait;
use App\Service\DoctrineTrait;
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
    use QueryTrait;
    use DoctrineTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Return all documents of group support.
     */
    public function findDocumentsQuery(DocumentSearch $search, User $user): Query
    {
        $qb = $this->createQueryBuilder('d')
            ->leftJoin('d.tags', 't')->addSelect('t')
            ->leftJoin('d.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->join('d.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->join('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->join('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->join('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}');

        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->where('d.createdBy = :user')
                ->setParameter('user', $user);
            $qb->orWhere('sg.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        $qb->andWhere('sg.id IS NULL OR sp.head = TRUE');

        if ($search->getId()) {
            $qb->andWhere('d.id = :id')
                ->setParameter('id', $search->getId());
        }
        if ($search->getName()) {
            $qb->andWhere('d.name LIKE :name OR d.content LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }

        if ($search->getStart()) {
            $qb->andWhere('d.start >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $qb->andWhere('d.start <= :end')
                ->setParameter('end', $search->getEnd());
        }

        $qb = $this->addOrganizationFilters($qb, $search);
        $qb = $this->addTagsFilter($qb, $search, 'd.tags');

        return $qb
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
        ;
    }

    /**
     * Return all documents of group support.
     */
    public function findSupportDocumentsQuery(SupportGroup $supportGroup, SupportDocumentSearch $search): Query
    {
        if ($search->getDeleted()) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        $qb = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.tags', 't')->addSelect('t')
            ->leftJoin('d.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')

            ->where('d.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);

        if ($search->getDeleted()) {
            $qb->andWhere('d.deletedAt IS NOT null');
        }
        if ($search->getName()) {
            $qb->andWhere('d.name LIKE :name OR d.content LIKE :name')
            ->setParameter('name', '%'.$search->getName().'%');
        }

        $this->addTagsFilter($qb, $search, 'd.tags');

        return $qb
            ->orderBy('d.createdAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
        ;
    }

    /**
     * Donne un document.
     */
    public function findDocument($id, bool $deleted = false): ?Document
    {
        if ($deleted) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        return $this->createQueryBuilder('d')->select('d')
            ->join('d.peopleGroup', 'pg')->addSelect('PARTIAL pg.{id}')
            ->join('d.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')

            ->andWhere('d.id IN (:id)')
            ->setParameter('id', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getSingleResult()
        ;
    }

    /**
     * Return all selected documents by id.
     *
     * @return Document[]
     */
    public function findDocumentsById(array $ids, SupportGroup $supportGroup): array
    {
        return $this->createQueryBuilder('d')->select('d')
            ->join('d.peopleGroup', 'pg')->addSelect('PARTIAL pg.{id}')
            ->join('d.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')

            ->andWhere('d.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)
            ->andWhere('d.id IN (:ids)')
            ->setParameter('ids', $ids)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * @return Document[]
     */
    public function findSoftDeletedDocuments(\DateTime $date): array
    {
        return $this->createQueryBuilder('d')->select('d')
            ->where('d.deletedAt  <= :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }

    public function countDocuments(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('d')->select('COUNT(d.id)');

        if ($criteria) {
            $qb->leftJoin('d.supportGroup', 'sg');

            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $qb = $this->addOrWhere($qb, 'sg.service', $value);
                }
                if ('subService' === $key) {
                    $qb = $this->addOrWhere($qb, 'sg.subService', $value);
                }
                if ('device' === $key) {
                    $qb = $this->addOrWhere($qb, 'sg.device', $value);
                }
                if ('status' === $key) {
                    $qb = $this->addOrWhere($qb, 'sg.status', $value);
                }
                if ('startDate' === $key) {
                    $qb->andWhere('d.createdAt >= :startDate')
                        ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere('d.createdAt <= :endDate')
                        ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $qb->andWhere('d.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function sumSizeAllDocuments(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('d')->select('SUM(d.size)');

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }
}
