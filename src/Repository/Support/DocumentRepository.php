<?php

namespace App\Repository\Support;

use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Model\Support\SupportDocumentSearch;
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

        if ($search->getPoles() && count($search->getPoles())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getPoles() as $pole) {
                $orX->add($expr->eq('s.pole', $pole));
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

        if ($search->getReferents() && count($search->getReferents())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

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

    public function countDocuments(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('d')->select('COUNT(d.id)');

        if ($criteria) {
            $query = $query->leftJoin('d.supportGroup', 'sg');

            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $query = $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' === $key) {
                    $query = $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' === $key) {
                    $query = $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
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
                if ('status' === $key) {
                    $query = $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
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
