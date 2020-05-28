<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Contribution;
use App\Security\CurrentUserService;
use App\Form\Model\ContributionSearch;
use Doctrine\Persistence\ManagerRegistry;
use App\Form\Model\SupportContributionSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Contribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contribution[]    findAll()
 * @method Contribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionRepository extends ServiceEntityRepository
{
    private $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, Contribution::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Donne toutes les participations financières à afficher.
     */
    public function findAllContributionsQuery(ContributionSearch $search): Query
    {
        $query = $this->getContributionsQuery();

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return  $query->orderBy('c.contribDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne toutes les participations financières à exporter.
     */
    public function findContributionsToExport(ContributionSearch $search): ?array
    {
        $query = $this->getContributionsQuery()
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}');

        $query = $this->filter($query, $search);

        return  $query->orderBy('c.contribDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la requête.
     */
    protected function getContributionsQuery()
    {
        return $this->createQueryBuilder('c')->select('c')
        ->leftJoin('c.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service}')
        ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
        ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head, person}')
        ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate}')
        ->leftJoin('c.createdBy', 'u')->addselect('PARTIAL u.{id, firstname, lastname}')
        ->leftJoin('c.updatedBy', 'u2')->addselect('PARTIAL u2.{id, firstname, lastname}');
    }

    /**
     * Filtre la recherche.
     */
    protected function filter($query, ContributionSearch $search)
    {
        if ($this->currentUser->getUser() && !$this->currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query->where('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        if ($search->getType()) {
            $query->andWhere('c.type = :type')
                ->setParameter('type', $search->getType());
        }

        if ($search->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }

        if ($search->getStart()) {
            $query->andWhere('c.contribDate >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('c.contribDate <= :end')
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
     * Return all contributions of group support.
     */
    public function findAllContributionsFromSupportQuery(int $supportGroupId, SupportContributionSearch $search): Query
    {
        $query = $this->createQueryBuilder('c')->select('c')
            ->andWhere('c.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getContributionId()) {
            $query->andWhere('c.id = :id')
                ->setParameter('id', $search->getContributionId());
        }
        if ($search->getType()) {
            $query->andWhere('c.type = :type')
                ->setParameter('type', $search->getType());
        }
        if ($search->getStart()) {
            $query->andWhere('c.contribDate >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('c.contribDate <= :end')
                ->setParameter('end', $search->getEnd());
        }

        $query = $query->orderBy('c.contribDate', 'DESC');

        return $query->getQuery();
    }

    /**
     * Compte le nombre de participations.
     *
     * @return mixed
     */
    public function countAllContributions(array $criteria = null)
    {
        $query = $this->createQueryBuilder('c')->select('COUNT(c.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('user' == $key) {
                    $query = $query->andWhere('c.createdBy = :user')
                        ->setParameter('user', $value);
                }
                if ('service' == $key) {
                    $query = $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Donne la somme des restants dûs de participations.
     *
     * @return mixed
     */
    public function sumStillDueAmt($supportId)
    {
        return $this->createQueryBuilder('c')->select('SUM(c.stillDueAmt)')
            ->andWhere('c.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
