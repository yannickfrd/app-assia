<?php

namespace App\Repository\Support;

use App\Entity\Organization\User;
use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\RdvSearch;
use App\Form\Model\Support\SupportRdvSearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rdv|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rdv|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rdv[]    findAll()
 * @method Rdv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RdvRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rdv::class);
    }

    /**
     * Return all rdvs of group support.
     */
    public function findRdvsQuery(RdvSearch $search, ?CurrentUserService $currentUser = null): Query
    {
        $query = $this->getRdvsQuery();

        $query = $this->filter($query, $search, $currentUser);

        return  $query->orderBy('r.start', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne un rendez-vous.
     */
    public function findRdv(int $id): ?Rdv
    {
        return $this->createQueryBuilder('r')->select('r')
            ->leftJoin('r.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('r.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')
            ->leftJoin('r.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id}')
            ->leftJoin('sg.referent2', 'ref2')->addSelect('PARTIAL ref2.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head}')

            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->where('r.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getSingleResult();
    }

    /**
     * Donne tous les RDVs à exporter.
     */
    public function findRdvsToExport(RdvSearch $search): ?array
    {
        $query = $this->getRdvsQuery()
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('r.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}');

        $query = $this->filter($query, $search);

        return  $query->orderBy('r.createdBy', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    protected function getRdvsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('r')->select('r')
            ->leftJoin('r.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('r.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}');
    }

    protected function filter($query, RdvSearch $search, CurrentUserService $currentUser = null): QueryBuilder
    {
        if ($currentUser && !$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->where('r.createdBy IN (:user)')
                ->setParameter('user', $currentUser->getUser());
            $query->orWhere('sg.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        if ($search->getId()) {
            return $query->andWhere('r.id = :id')
                ->setParameter('id', $search->getId());
        }

        if ($search->getTitle()) {
            $query->andWhere('r.title LIKE :title')
                ->setParameter('title', '%'.$search->getTitle().'%');
        }

        if ($search->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }

        if ($search->getStart()) {
            $query->andWhere('r.start >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('r.start <= :end')
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

        return $query;
    }

    /**
     * Return all rdvs of group support.
     */
    public function findRdvsQueryOfSupport(int $supportGroupId, SupportRdvSearch $search): Query
    {
        $query = $this->createQueryBuilder('r')->select('r')
            ->leftJoin('r.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('r.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')

            ->andWhere('sg.id = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getTitle()) {
            $query->andWhere('r.title LIKE :title')
                ->setParameter('title', '%'.$search->getTitle().'%');
        }

        if ($search->getStart()) {
            $query->andWhere('r.start >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('r.start <= :end')
                ->setParameter('end', $search->getEnd());
        }

        return  $query->orderBy('r.createdAt', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    public function findLastRdvOfSupport(int $supportGroupId): ?Rdv
    {
        return $this->createQueryBuilder('r')->select('r')

            ->andWhere('r.supportGroup= :supportGroup')
            ->setParameter('supportGroup', $supportGroupId)
            ->andWhere('r.start <=  :now')
            ->setParameter('now', new \DateTime())

            ->setMaxResults(1)
            ->orderBy('r.start', 'DESC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    public function findNextRdvOfSupport(int $supportGroupId): ?Rdv
    {
        return $this->createQueryBuilder('r')->select('r')

            ->andWhere('r.supportGroup= :supportGroup')
            ->setParameter('supportGroup', $supportGroupId)
            ->andWhere('r.start >  :now')
            ->setParameter('now', new \DateTime())

            ->setMaxResults(1)
            ->orderBy('r.start', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Trouve tous les RDV entre 2 dates.
     *
     * @return Rdv[]|null
     */
    public function findRdvsBetween(\Datetime $start, \Datetime $end, SupportGroup $supportGroup = null, User $user = null): ?array
    {
        $query = $this->createQueryBuilder('r')->select('r')
            ->leftJoin('r.createdBy', 'u')->addSelect('u')
            ->leftJoin('r.supportGroup', 's')->addSelect('s')

            ->where('r.start >= :start')->setParameter('start', $start)
            ->andWhere('r.start <= :end')->setParameter('end', $end);

        if ($supportGroup) {
            $query->andWhere('r.supportGroup = :supportGroup')->setParameter('supportGroup', $supportGroup);
        } else {
            $query->andWhere('r.createdBy = :user')
                ->setParameter('user', $user);
        }

        return $query->orderBy('r.start', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les RDV entre 2 dates par jour.
     */
    public function findRdvsBetweenByDay(\Datetime $start, \Datetime $end, SupportGroup $supportGroup = null, User $user = null): array
    {
        $rdvs = $this->findRdvsBetween($start, $end, $supportGroup, $user);
        $days = [];

        foreach ($rdvs as $rdv) {
            $date = $rdv->getStart()->format('Y-m-d');
            if (!isset($days[$date])) {
                $days[] = $date;
            }
            $days[$date][] = $rdv;
        }

        return $days;
    }

    /**
     * Donne tous les rdvs créés par l'utilisateur.
     *
     * @return Rdv[]|null
     */
    public function findRdvsOfUser(User $user, int $maxResults = 1000): ?array
    {
        return $this->createQueryBuilder('rdv')->addSelect('rdv')
            ->leftJoin('rdv.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('rdv.createdBy = :createdBy')
            ->setParameter('createdBy', $user)
            ->andWhere('rdv.start >= :start')
            ->setParameter('start', (new \DateTime())->modify('-1 hour'))
            // ->andWhere('sp.head = TRUE')

            ->orderBy('rdv.start', 'DESC')

            ->setMaxResults($maxResults)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Compte le nombre de RDV selon des critères.
     */
    public function countRdvs(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('rdv')->select('COUNT(rdv.id)');

        if ($criteria) {
            $query = $query->leftJoin('rdv.supportGroup', 'sg');

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
                    $query = $query->andWhere('rdv.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere('rdv.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $query = $query->andWhere('rdv.createdBy = :createdBy')
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
}
