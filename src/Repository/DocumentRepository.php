<?php

namespace App\Repository;

use App\Entity\Document;
use App\Form\Model\DocumentSearch;
use App\Form\Model\SupportDocumentSearch;
use App\Security\CurrentUserService;
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
    public function findDocumentsQuery(DocumentSearch $search, CurrentUserService $currentUser = null): Query
    {
        $query = $this->createQueryBuilder('d')
            ->leftJoin('d.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('d.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}');

        $query = $this->filters($query, $search);

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

        if ($search->getReferents() && count($search->getReferents())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

        if ($search->getServices() && count($search->getServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($search->getSubServices() && count($search->getSubServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getSubServices() as $subService) {
                $orX->add($expr->eq('sg.subService', $subService));
            }
            $query->andWhere($orX);
        }

        if ($search->getDevices() && count($search->getDevices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        $query->orderBy('d.createdAt', 'DESC');

        return $query->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Return all documents of group support.
     */
    public function findSupportDocumentsQuery(int $supportGroupId, SupportDocumentSearch $search): Query
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

    protected function filters($query, $search)
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
