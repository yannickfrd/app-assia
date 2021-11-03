<?php

namespace App\Repository\Support;

use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Model\Support\SupportDocumentSearch;
use App\Repository\Traits\QueryTrait;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Document::class);
    }

    /**
     * Return all documents of group support.
     */
    public function findDocumentsQuery(DocumentSearch $search, CurrentUserService $currentUser = null): Query
    {
        $query = $this->createQueryBuilder('d')->select('d')
            ->leftJoin('d.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->join('d.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->join('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->join('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->join('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}');

        if ($currentUser && !$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->where('d.createdBy IN (:user)')
                ->setParameter('user', $currentUser->getUser());
            $query->orWhere('sg.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($search->getId()) {
            $query->andWhere('d.id = :id')
                ->setParameter('id', $search->getId());
        }

        if ($search->getStart()) {
            $query->andWhere('d.start >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('d.start <= :end')
                ->setParameter('end', $search->getEnd());
        }

        $query = $this->addOrganizationFilters($query, $search);

        $query = $this->filters($query, $search);

        $query->orderBy('d.createdAt', 'DESC');

        return $query->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Return all documents of group support.
     */
    public function findSupportDocumentsQuery(SupportGroup $supportGroup, SupportDocumentSearch $search): Query
    {
        $query = $this->createQueryBuilder('d')->select('d')
            // ->leftJoin('d.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service}')
            ->leftJoin('d.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')

            ->where('d.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);

        // if ($user && !in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
        //     $query->andWhere('sg.service IN (:services)')
        //         ->setParameter('services', $user->getServices());
        // }

        if ($search->getName()) {
            $query->andWhere('d.name LIKE :name OR d.content LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getType()) {
            $query->andWhere('d.type = :type')
                ->setParameter('type', $search->getType());
        }
        $query = $query->orderBy('d.createdAt', 'DESC');

        return $query->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Return all selected documents by id.
     *
     * @return Documents[]
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

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
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
        $query = $this->createQueryBuilder('d')->select('COUNT(d.id)');

        if ($criteria) {
            $query = $query->leftJoin('d.supportGroup', 'sg');

            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $query = $this->addOrWhere($query, 'sg.service', $value);
                }
                if ('subService' === $key) {
                    $query = $this->addOrWhere($query, 'sg.subService', $value);
                }
                if ('device' === $key) {
                    $query = $this->addOrWhere($query, 'sg.device', $value);
                }
                if ('status' === $key) {
                    $query = $this->addOrWhere($query, 'sg.status', $value);
                }
                if ('startDate' === $key) {
                    $query = $query->andWhere('d.createdAt >= :startDate')
                        ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere('d.createdAt <= :endDate')
                        ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $query = $query->andWhere('d.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    public function sumSizeAllDocuments(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('d')->select('SUM(d.size)');

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    protected function filters($query, $search): QueryBuilder
    {
        if ($search->getName()) {
            $query->andWhere('d.name LIKE :name OR d.content LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getType()) {
            $query->andWhere('d.type = :type')
                ->setParameter('type', $search->getType());
        }

        return $query;
    }
}
