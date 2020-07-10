<?php

namespace App\Repository;

use App\Entity\Rdv;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Form\Model\RdvSearch;
use App\Form\Model\SupportRdvSearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Rdv|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rdv|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rdv[]    findAll()
 * @method Rdv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RdvRepository extends ServiceEntityRepository
{
    private $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, Rdv::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Return all rdvs of group support.
     */
    public function findAllRdvsQuery(RdvSearch $search): Query
    {
        $query = $this->getRdvsQuery();

        $query = $this->filter($query, $search);

        return  $query->orderBy('r.start', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne tous les RDVs à exporter.
     */
    public function findRdvsToExport(RdvSearch $search): ?array
    {
        $query = $this->getRdvsQuery()
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, pole}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('r.updatedBy', 'u2')->addselect('PARTIAL u2.{id, firstname, lastname}');

        $query = $this->filter($query, $search);

        return  $query->orderBy('r.createdBy', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    protected function getRdvsQuery()
    {
        return $this->createQueryBuilder('r')->select('r')
            ->leftJoin('r.createdBy', 'u')->addselect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('r.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}');
    }

    protected function filter($query, RdvSearch $search)
    {
        if ($this->currentUser->getUser() && !$this->currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query->where('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
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

        if ($search->getDevices() && count($search->getDevices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        return $query;
    }

    /**
     * Return all rdvs of group support.
     */
    public function findAllRdvsQueryFromSupport(int $supportGroupId, SupportRdvSearch $search): Query
    {
        $query = $this->createQueryBuilder('r')
            ->select('r')
            ->leftJoin('r.createdBy', 'u')->addselect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('r.supportGroup', 'sg')->addSelect('sg')

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

    /**
     * Trouve tous les RDV entre 2 dates.
     *
     * @return mixed
     */
    public function findRdvsBetween(\Datetime $start, \Datetime $end, SupportGroup $supportGroup = null)
    {
        $query = $this->createQueryBuilder('r')->select('r')
            ->leftJoin('r.createdBy', 'u')->addselect('u')
            ->leftJoin('r.supportGroup', 's')->addselect('s')

            ->where('r.start >= :start')->setParameter('start', $start)
            ->andWhere('r.start <= :end')->setParameter('end', $end);

        if ($supportGroup) {
            $query->andWhere('r.supportGroup = :supportGroup')->setParameter('supportGroup', $supportGroup);
        } else {
            $query->andWhere('r.createdBy = :user')
                ->setParameter('user', $this->currentUser->getUser());
        }

        return $query->orderBy('r.start', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les RDV entre 2 dates par jour.
     */
    public function findRdvsBetweenByDay(\Datetime $start, \Datetime $end, SupportGroup $supportGroup = null): array
    {
        $rdvs = $this->findRdvsBetween($start, $end, $supportGroup);
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
     * @return mixed
     */
    public function findAllRdvsFromUser(User $user, int $maxResults = 1000)
    {
        return $this->createQueryBuilder('rdv')->addselect('rdv')
            ->leftJoin('rdv.supportGroup', 'sg')->addselect('PARTIAL sg.{id}')
            ->leftJoin('sg.supportPeople', 'sp')->addselect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('rdv.createdBy = :createdBy')
            ->setParameter('createdBy', $user)
            ->andWhere('rdv.start >= :start')
            ->setParameter('start', (new \DateTime())->modify('-1 hour'))

            ->orderBy('rdv.start', 'DESC')

            ->setMaxResults($maxResults)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function countAllRdvs(array $criteria = null)
    {
        $query = $this->createQueryBuilder('rdv')->select('COUNT(rdv.id)');

        if ($criteria) {
            // $query = $query->leftJoin("rdv.supportGroup", "sg")->addselect("PARTIAL sg.{id, referent, status, service, device}");

            foreach ($criteria as $key => $value) {
                if ('user' == $key) {
                    $query = $query->andWhere('rdv.createdBy = :user')
                        ->setParameter('user', $value);
                }
                if ('status' == $key) {
                    $query = $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
                if ('service' == $key) {
                    $query = $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('device' == $key) {
                    $query = $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
