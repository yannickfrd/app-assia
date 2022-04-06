<?php

namespace App\Repository\Event;

use App\Entity\Event\Rdv;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Event\EventSearch;
use App\Repository\Traits\QueryTrait;
use App\Service\DoctrineTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Rdv|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rdv|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rdv[]    findAll()
 * @method Rdv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RdvRepository extends ServiceEntityRepository
{
    use QueryTrait;
    use DoctrineTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rdv::class);
    }

    /**
     * Return all rdvs of group support.
     */
    public function findRdvsQuery(EventSearch $search, User $user, ?SupportGroup $supportGroup = null): Query
    {
        if ($search->getDeleted()) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }
        $qb = $this->getRdvsQuery();

        if ($search->getDeleted()) {
            $qb->andWhere('r.deletedAt IS NOT null');
        }

        return $this->filter($qb, $search, $user, $supportGroup)
            ->orderBy('r.start', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
        ;
    }

    /**
     * Donne un rendez-vous.
     */
    public function findRdv(int $id, bool $deleted = false): ?Rdv
    {
        if ($deleted) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        return $this->getBaseQuery()
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head}')
            ->leftJoin('r.updatedBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id}')
            ->leftJoin('sg.referent2', 'ref2')->addSelect('PARTIAL ref2.{id}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->where('r.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult()
        ;
    }

    /**
     * Donne tous les RDVs à exporter.
     */
    public function findRdvsToExport(EventSearch $search, UserInterface $user, ?SupportGroup $supportGroup = null): array
    {
        $qb = $this->getRdvsQuery()
            ->leftJoin('r.updatedBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}');

        $qb = $this->filter($qb, $search, $user, $supportGroup);

        return $qb
            ->orderBy('r.createdBy', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    protected function getBaseQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('r')->select('r')
            ->leftJoin('r.users', 'u1')->addSelect('PARTIAL u1.{id, firstname, lastname}')
            ->leftJoin('r.tags', 't')->addSelect('t')
            ->leftJoin('r.alerts', 'a')->addSelect('a')
            ->leftJoin('r.createdBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')
            ->leftJoin('r.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
        ;
    }

    protected function getRdvsQuery(): QueryBuilder
    {
        return $this->getBaseQuery()
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')
        ;
    }

    protected function filter(QueryBuilder $qb, EventSearch $search, UserInterface $user, ?SupportGroup $supportGroup =
    null): QueryBuilder
    {
        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->where('r.createdBy = :user')
                ->setParameter('user', $user);
            $qb->orWhere('sg.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        $qb->andWhere('sg.id IS NULL OR sp.head = TRUE');

        if ($supportGroup) {
            $qb->andWhere('sg.id = :supportGroup')
            ->setParameter('supportGroup', $supportGroup->getId());
        }

        if ($search->getId()) {
            return $qb->andWhere('r.id = :id')
                ->setParameter('id', $search->getId());
        }

        if ($search->getTitle()) {
            $qb->andWhere('r.title LIKE :title')
                ->setParameter('title', '%'.$search->getTitle().'%');
        }

        if ($search->getFullname()) {
            $qb->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }

        if ($search->getStart()) {
            $qb->andWhere('r.start >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $qb->andWhere('r.start <= :end')
                ->setParameter('end', $search->getEnd());
        }
        if ($search->getUsers() && $search->getUsers()->count() > 0) {
            $qb
                ->leftJoin('r.users', 'u3')
                ->andWhere('u3.id in (:users)')
                ->setParameter('users', $search->getUsers());
        }

        $qb = $this->addOrganizationFilters($qb, $search);
        $qb = $this->addTagsFilter($qb, $search, 'r.tags');

        return $qb;
    }

    /**
     * Return all rdvs of group support.
     */
    public function findRdvsQueryOfSupport(EventSearch $search, SupportGroup $supportGroup): Query
    {
        if ($search->getDeleted()) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        $qb = $this->getBaseQuery()
            ->andWhere('sg.id = :supportGroup')
            ->setParameter('supportGroup', $supportGroup->getId());

        if ($search->getDeleted()) {
            $qb->andWhere('r.deletedAt IS NOT null');
        }
        if ($search->getTitle()) {
            $qb->andWhere('r.title LIKE :title')
                ->setParameter('title', '%'.$search->getTitle().'%');
        }

        if ($search->getStart()) {
            $qb->andWhere('r.start >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $qb->andWhere('r.start <= :end')
                ->setParameter('end', $search->getEnd());
        }

        $this->addTagsFilter($qb, $search, 'r.tags');

        return $qb
            ->orderBy('r.start', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
        ;
    }

    public function findLastRdvOfSupport(int $supportGroupId): ?Rdv
    {
        return $this->createQueryBuilder('r')->select('r')

            ->andWhere('r.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId)
            ->andWhere('r.start <= :now')
            ->setParameter('now', new \DateTime())

            ->setMaxResults(1)
            ->orderBy('r.start', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult()
        ;
    }

    public function findNextRdvOfSupport(int $supportGroupId): ?Rdv
    {
        return $this->createQueryBuilder('r')->select('r')

            ->andWhere('r.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId)
            ->andWhere('r.start >  :now')
            ->setParameter('now', new \DateTime())

            ->setMaxResults(1)
            ->orderBy('r.start', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult()
        ;
    }

    /**
     * Trouve tous les RDV entre 2 dates.
     *
     * @return Rdv[]
     */
    public function findRdvsBetween(\DateTime $start, \DateTime $end, SupportGroup $supportGroup = null, User $user = null): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.start BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
        ;

        if ($supportGroup) {
            $qb->leftJoin('r.supportGroup', 's')
                ->andWhere('r.supportGroup = :supportGroup')->setParameter('supportGroup', $supportGroup);
        } else {
            $qb->leftJoin('r.users', 'u')
                ->andWhere('u.id = :user')
                ->setParameter('user', $user->getId());
        }

        return $qb
            ->orderBy('r.start', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    /**
     * Donne tous les RDV entre 2 dates par jour.
     */
    public function findRdvsBetweenByDay(\DateTime $start, \DateTime $end, SupportGroup $supportGroup = null, User $user = null): array
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
     * Donne tous les rdvs rattachés à l'utilisateur.
     *
     * @return Rdv[]
     */
    public function findRdvsOfUser(User $user, int $maxResults = 100): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.users', 'u')
            ->leftJoin('r.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')
            ->leftJoin('r.alerts', 'a')->addSelect('a')

            ->andWhere('sg.id IS NULL OR sp.head = TRUE')

            ->andWhere('u.id = :user')
            ->setParameter('user', $user->getId())

            ->andWhere('r.start >= :start')
            ->setParameter('start', (new \DateTime())->modify('-1 hour'))

            ->orderBy('r.start', 'ASC')
            ->setMaxResults($maxResults)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    /**
     * Compte le nombre de RDV selon des critères.
     */
    public function countRdvs(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('rdv')->select('COUNT(rdv.id)');

        if ($criteria) {
            $qb->leftJoin('rdv.supportGroup', 'sg');

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
                    $qb->andWhere('rdv.createdAt >= :startDate')
                        ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere('rdv.createdAt <= :endDate')
                        ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $qb->andWhere('rdv.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}
