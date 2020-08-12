<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\Contribution;
use App\Entity\SupportGroup;
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

        return  $query->orderBy('c.monthContrib', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Trouve tous les RDV entre 2 dates.
     *
     * @return mixed
     */
    public function findContributionsBetween(\Datetime $start, \Datetime $end, array $supportsId)
    {
        $query = $this->createQueryBuilder('c')->select('c')
            ->leftJoin('c.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')

            ->andWhere('c.supportGroup IN (:supportsId)')
            ->setParameter('supportsId', $supportsId)

            ->andWhere('c.monthContrib >= :start')
            ->setParameter('start', $start)
            ->andWhere('c.monthContrib <= :end')
            ->setParameter('end', $end);

        return $query->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les participations financières à exporter.
     *
     * @param ContributionSearch|SupportContributionSearch $search
     */
    public function findContributionsToExport($search, SupportGroup $supportGroup = null): ?array
    {
        $query = $this->getContributionsQuery()
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}');

        if ($supportGroup) {
            $query->where('c.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);
        }

        $query = $this->filter($query, $search);

        return  $query->orderBy('c.monthContrib', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function findAllContributionsForIndicators(ContributionSearch $search = null): ?array
    {
        $query = $this->createQueryBuilder('c')->select('c')
            ->leftJoin('c.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, device}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head, person}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}');

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la requête.
     */
    protected function getContributionsQuery()
    {
        return $this->createQueryBuilder('c')->select('c') // 'c', '(c.toPayAmt - c.paidAmt) AS stillToPayAmt'
        ->leftJoin('c.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, startDate, endDate}')
        ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
        ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
        ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head, person}')
        ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate}')
        ->leftJoin('c.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
        ->leftJoin('c.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}');
    }

    /**
     * Filtre la recherche.
     *
     * @param ContributionSearch|SupportContributionSearch $search
     */
    protected function filter($query, $search)
    {
        if ($this->currentUser->getUser() && !$this->currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        if ($search->getType()) {
            $query->andWhere('c.type IN (:type)')
                ->setParameter('type', $search->getType());
        }

        switch ($search->getDateType()) {
            case 1:
                $dateType = 'paymentDate';
                break;
            case 2:
                $dateType = 'monthContrib';
                break;
            default:
                $dateType = 'createdAt';
                break;
        }

        if ($search->getStart()) {
            $query->andWhere('c.'.$dateType.' >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('c.'.$dateType.' <= :end')
                ->setParameter('end', $search->getEnd());
        }

        if ($search instanceof ContributionSearch) {
            if ($search->getFullname()) {
                $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
            }

            if ($search->getReferents() && $search->getReferents()->count()) {
                $expr = $query->expr();
                $orX = $expr->orX();
                foreach ($search->getReferents() as $referent) {
                    $orX->add($expr->eq('sg.referent', $referent));
                }
                $query->andWhere($orX);
            }

            if ($search->getServices() && $search->getServices()->count()) {
                $expr = $query->expr();
                $orX = $expr->orX();
                foreach ($search->getServices() as $service) {
                    $orX->add($expr->eq('sg.service', $service));
                }
                $query->andWhere($orX);
            }

            if ($search->getDevices() && $search->getDevices()->count()) {
                $expr = $query->expr();
                $orX = $expr->orX();
                foreach ($search->getDevices() as $device) {
                    $orX->add($expr->eq('sg.device', $device));
                }
                $query->andWhere($orX);
            }
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

        // if ($search->getContributionId()) {
        //     $query->andWhere('c.id = :id')
        //         ->setParameter('id', $search->getContributionId());
        // }
        if ($search->getType()) {
            $query->andWhere('c.type IN (:type)')
                ->setParameter('type', $search->getType());
        }
        if ($search->getStart()) {
            $query->andWhere('c.monthContrib >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('c.monthContrib <= :end')
                ->setParameter('end', $search->getEnd());
        }

        $query = $query->orderBy('c.createdAt', 'DESC');

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
     * Donne la somme des restants dus de participations.
     *
     * @return mixed
     */
    public function sumStillToPayAmt($supportId)
    {
        return $this->createQueryBuilder('c')->select('SUM(c.stillToPayAmt)')
            ->andWhere('c.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
