<?php

namespace App\Repository\Support;

use App\Entity\Support\Contribution;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\ContributionSearch;
use App\Form\Model\Support\SupportContributionSearch;
use App\Repository\Traits\QueryTrait;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Contribution|null find($id, $lockMode = null, $lockVersion = null)
 * @method Contribution|null findOneBy(array $criteria, array $orderBy = null)
 * @method Contribution[]    findAll()
 * @method Contribution[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ContributionRepository extends ServiceEntityRepository
{
    use QueryTrait;

    private $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, Contribution::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Donne une contribution.
     */
    public function findContribution(int $id): ?Contribution
    {
        return $this->createQueryBuilder('c')->select('c')
            ->leftJoin('c.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, startDate, endDate, address, city}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1, contribution, contributionType, contributionRate, city, address}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name, email, phone1}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name, logoPath}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender, email}')

            ->where("c.id = $id")

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne toutes les participations financières à afficher.
     */
    public function findContributionsQuery(?ContributionSearch $search = null): Query
    {
        $query = $this->getContributionQuery()
            ->andWhere('sp.head = TRUE');

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->orderBy('c.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Trouve tous les RDV entre 2 dates.
     *
     * @return Contribution[]|null
     */
    public function findContributionsBetween(\Datetime $start, \Datetime $end, array $supportsId): ?array
    {
        $query = $this->createQueryBuilder('c')->select('c')
            ->leftJoin('c.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')

            ->andWhere('c.supportGroup IN (:supportsId)')
            ->setParameter('supportsId', $supportsId)

            ->andWhere('c.startDate >= :start')
            ->setParameter('start', $start)
            ->andWhere('c.startDate <= :end')
            ->setParameter('end', $end);

        return $query->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les participations financières à exporter.
     *
     * @param ContributionSearch|SupportContributionSearch $search
     *
     * @return Contribution[]|null
     */
    public function findContributionsToExport($search, SupportGroup $supportGroup = null): ?array
    {
        $query = $this->getContributionQuery()
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}');

        if ($supportGroup) {
            $query->where('c.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);
        }

        $query = $this->filter($query, $search);

        return $query->orderBy('c.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne les contributions pour les indicateurs stats.
     *
     * @return Contribution[]|null
     */
    public function findContributionsForIndicators(ContributionSearch $search = null): ?array
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
    protected function getContributionQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('c')->select('c') // 'c', '(c.toPayAmt - c.paidAmt) AS stillToPayAmt'
        ->leftJoin('c.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, startDate, endDate}')
        ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
        ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
        ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head}')
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
        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        // if ($search->getId()) {
        //     $query->andWhere('c.id = :id')
        //         ->setParameter('id', $search->getId());
        // }

        if ($search->getType()) {
            $query->andWhere('c.type IN (:type)')
                ->setParameter('type', $search->getType());
        }

        if (in_array($search->getDateType(), [1, 3], true)) {
            if (1 === $search->getDateType()) {
                $dateType = 'paymentDate';
            } else {
                $dateType = 'createdAt';
            }
            if ($search->getStart()) {
                $query->andWhere('c.'.$dateType.' >= :start')
                ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('c.'.$dateType.' <= :end')
                ->setParameter('end', $search->getEnd());
            }
        } elseif (2 === $search->getDateType()) {
            if ($search->getStart()) {
                $query->andWhere('c.endDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('c.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search instanceof ContributionSearch) {
            if ($search->getFullname()) {
                $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
            }

            $query = $this->addOrganizationFilters($query, $search);
        }

        return $query;
    }

    /**
     * Return all contributions of group support.
     */
    public function findContributionsOfSupportQuery(int $supportGroupId, SupportContributionSearch $search): Query
    {
        $query = $this->createQueryBuilder('c')->select('c')
            ->andWhere('c.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getType()) {
            $query->andWhere('c.type IN (:type)')
                ->setParameter('type', $search->getType());
        }
        if ($search->getStart()) {
            $query->andWhere('c.startDate >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('c.startDate <= :end')
                ->setParameter('end', $search->getEnd());
        }

        $query = $query->orderBy('c.createdAt', 'DESC');

        return $query->getQuery();
    }

    /**
     * Compte le nombre de contributions financières.
     */
    public function countContributions(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('c')->select('COUNT(c.id)');

        if ($criteria) {
            $query = $query->leftJoin('c.supportGroup', 'sg');

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
                    $query = $query->andWhere('c.createdAt >= :startDate')
                        ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere('c.createdAt <= :endDate')
                        ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $query = $query->andWhere('c.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Donne la somme des restants dus de participations.
     */
    public function sumStillToPayAmt($supportId): int
    {
        return $this->createQueryBuilder('c')->select('SUM(c.stillToPayAmt)')
            ->andWhere('c.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
