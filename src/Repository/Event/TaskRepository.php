<?php

namespace App\Repository\Event;

use App\Entity\Event\Task;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Event\TaskSearch;
use App\Repository\Traits\QueryTrait;
use App\Service\DoctrineTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    use QueryTrait;
    use DoctrineTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * Return all tasks to paginate.
     */
    public function findTasksQuery(TaskSearch $search, User $user, ?SupportGroup $supportGroup = null): Query
    {
        if ($search->getDeleted()) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        $qb = $this->getTasksQuery();
        $qb = $this->filter($qb, $search, $user);

        if ($supportGroup) {
            $qb->andWhere('t.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);
        }

        if ($search->getDeleted()) {
            $qb->andWhere('t.deletedAt IS NOT null');
        }

        return $qb
            ->orderBy('t.end', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
        ;
    }

    /**
     * Donne une tâche.
     */
    public function findTask(int $id, bool $deleted = false): ?Task
    {
        if ($deleted) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        return $this->createQueryBuilder('t')
            ->leftJoin('t.users', 'u1')->addSelect('PARTIAL u1.{id, firstname, lastname}')
            ->leftJoin('t.createdBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')
            ->leftJoin('t.updatedBy', 'u3')->addSelect('PARTIAL u3.{id, firstname, lastname}')
            ->leftJoin('t.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp1')->addSelect('PARTIAL sp1.{id, head}')
            ->leftJoin('sp1.person', 'p1')->addSelect('PARTIAL p1.{id, firstname, lastname}')
            // ->leftJoin('t.supportPeople', 'sp2')->addSelect('PARTIAL sp2.{id, head}')
            // ->leftJoin('sp2.person', 'p2')->addSelect('PARTIAL p2.{id, firstname, lastname}')
            ->leftJoin('t.tags', 'tags')->addSelect('tags')
            ->leftJoin('t.alerts', 'a')->addSelect('a')

            ->where('t.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult()
        ;
    }

    /**
     * Donne tous les évenements à exporter.
     */
    public function findTasksToExport(TaskSearch $search, User $user): array
    {
        $qb = $this->getTasksQuery()
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('t.updatedBy', 'u4')->addSelect('PARTIAL u4.{id, firstname, lastname}');

        $qb = $this->filter($qb, $search, $user);

        return $qb
            ->orderBy('t.createdBy', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    protected function getTasksQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('t     ')->select('t')
            ->leftJoin('t.users', 'u1')->addSelect('PARTIAL u1.{id, firstname, lastname}')
            ->leftJoin('t.createdBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')
            ->leftJoin('t.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')
            ->leftJoin('t.alerts', 'a')->addSelect('a')
            ->leftJoin('t.tags', 'tags')->addSelect('tags')
        ;
    }

    /**
     * Donne toutes les tâches à faire de l'utilisateur.
     *
     * @return Task[]
     */
    public function findActiveTasksOfUser(User $user, int $maxResults = 100): array
    {
        return $this->createQueryBuilder('t')->select('t')
            ->join('t.users', 'u')
            ->leftJoin('t.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')
            ->leftJoin('t.alerts', 'a')->addSelect('a')
            // ->groupBy('t.id')

            ->where('t.status = FALSE')
            ->andWhere('sg.id IS NULL OR sp.head = TRUE')
            ->andWhere('u.id = :user')
            ->setParameter('user', $user)

            ->orderBy('t.end', 'ASC')
            ->setMaxResults($maxResults)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    protected function filter(QueryBuilder $qb, TaskSearch $search, User $user): QueryBuilder
    {
        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->where('t.createdBy IN (:user)')
                ->setParameter('user', $user);
            $qb->orWhere('sg.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }
        if ($search->getId()) {
            return $qb->andWhere('t.id = :id')
                ->setParameter('id', $search->getId());
        }
        if ($search->getTitle()) {
            $qb->andWhere('t.title LIKE :title')
                ->setParameter('title', '%'.$search->getTitle().'%');
        }
        if ($search->getFullname()) {
            $qb->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }
        if ($search->getStart()) {
            $qb->andWhere('t.end >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $qb->andWhere('t.end <= :end')
                ->setParameter('end', $search->getEnd());
        }
        if ($search->getSupportGroup()) {
            $qb->andWhere('t.supportGroup = :supportGroup')
                ->setParameter('supportGroup', $search->getSupportGroup());
        }
        if ($search->getStatus()) {
            $qb->andWhere('t.status IN (:status)')
                ->setParameter('status', $search->getStatus());
        }
        if ($search->getLevel()) {
            $qb->andWhere('t.level IN (:level)')
                ->setParameter('level', $search->getLevel());
        }

        $qb = $this->addPolesFilter($qb, $search);
        $qb = $this->addServicesFilter($qb, $search);
        $qb = $this->addSubServicesFilter($qb, $search);
        $qb = $this->addDevicesFilter($qb, $search);
        $qb = $this->addTagsFilter($qb, $search, 't.tags');

        if ($search->getUsers() && $search->getUsers()->count() > 0) {
            $qb
                ->leftJoin('t.users', 'u3')
                ->andWhere('u3.id in (:users)')
                ->setParameter('users', $search->getUsers())
            ;
        }

        return $qb;
    }

    /*
     * Return all tasks of group support.
     */
    public function findTasksQueryOfSupport(int $supportGroupId, TaskSearch $search): Query
    {
        $qb = $this->createQueryBuilder('t')->select('t')
            ->leftJoin('t.users', 'u1')->addSelect('PARTIAL u1.{id, firstname, lastname}')
            ->leftJoin('t.createdBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')
            ->leftJoin('t.supportGroup', 'sg')->addSelect('sg')

            ->andWhere('sg.id = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getTitle()) {
            $qb->andWhere('t.title LIKE :title')
                ->setParameter('title', '%'.$search->getTitle().'%');
        }

        if ($search->getStart()) {
            $qb->andWhere('t.start >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $qb->andWhere('t.start <= :end')
                ->setParameter('end', $search->getEnd());
        }

        return $qb
            ->orderBy('t.start', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
        ;
    }

    /**
     * Compte le nombre de Tasks selon des critères.
     */
    public function countTasks(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('t')->select('COUNT(t.id)');

        if ($criteria) {
            $qb->leftJoin('t.supportGroup', 'sg');

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
                if ('start' === $key) {
                    $qb->andWhere('t.createdAt >= :startDate')
                        ->setParameter('startDate', $value);
                }
                if ('end' === $key) {
                    $qb->andWhere('t.createdAt <= :endDate')
                        ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $qb->andWhere('t.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getSingleScalarResult()
        ;
    }
}
