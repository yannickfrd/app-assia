<?php

namespace App\Repository\Support;

use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\PaymentSearch;
use App\Form\Model\Support\SupportPaymentSearch;
use App\Repository\Traits\QueryTrait;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    use QueryTrait;

    private $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, Payment::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Donne un paiement.
     */
    public function findPayment(int $id): ?Payment
    {
        return $this->createQueryBuilder('p')->select('p')
            ->leftJoin('p.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, status, startDate, endDate, address, city}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1, contribution, contributionType, contributionRate, city, address}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name, email, phone1}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name, logoPath}')
            ->leftJoin('pole.organization', 'o')->addSelect('PARTIAL o.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head, status}')
            ->leftJoin('sp.person', 'person')->addSelect('PARTIAL person.{id, firstname, lastname, birthdate, gender, email}')

            ->where("p.id = $id")

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne toutes les participations financières à afficher.
     */
    public function findPaymentsQuery(?PaymentSearch $search = null): Query
    {
        $query = $this->getPaymentQuery()
            ->andWhere('sp.head = TRUE');

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->orderBy('p.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Trouve tous les RDV entre 2 dates.
     *
     * @return Payment[]|null
     */
    public function findPaymentsBetween(\Datetime $start, \Datetime $end, array $supportsId): ?array
    {
        $query = $this->createQueryBuilder('p')->select('p')
            ->leftJoin('p.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')

            ->andWhere('p.supportGroup IN (:supportsId)')
            ->setParameter('supportsId', $supportsId)

            ->andWhere('p.startDate >= :start')
            ->setParameter('start', $start)
            ->andWhere('p.startDate <= :end')
            ->setParameter('end', $end);

        return $query->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les participations financières à exporter.
     *
     * @param PaymentSearch|SupportPaymentSearch $search
     *
     * @return Payment[]|null
     */
    public function findPaymentsToExport($search, SupportGroup $supportGroup = null): ?array
    {
        $query = $this->getPaymentQuery()
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, siSiaoId}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}');

        if ($supportGroup) {
            $query->where('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);
        }

        $query = $this->filter($query, $search);

        return $query->orderBy('p.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne toutes les PAF hôtel à exporter pour DELTA.
     *
     * @param PaymentSearch|SupportPaymentSearch $search
     *
     * @return Payment[]|null
     */
    public function findHotelContributionsToExport($search, SupportGroup $supportGroup = null): ?array
    {
        $query = $this->getPaymentQuery()
            ->leftJoin('sg.originRequest', 'or')->addSelect('PARTIAL or.{id}')
            ->leftJoin('or.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, siSiaoId}');

        if ($supportGroup) {
            $query->where('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);
        }

        $query = $this->filter($query, $search);

        return $query->orderBy('p.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne les paiements pour les indicateurs stats.
     *
     * @return Payment[]|null
     */
    public function findPaymentsForIndicators(PaymentSearch $search = null): ?array
    {
        $query = $this->createQueryBuilder('p')->select('p')
            ->leftJoin('p.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, device}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head, person}')
            ->leftJoin('sp.person', 'person')->addSelect('PARTIAL person.{id, firstname, lastname}')
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
    protected function getPaymentQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')->select('p') // 'p', '(p.toPayAmt - p.paidAmt) AS stillToPayAmt'
        ->leftJoin('p.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, startDate, endDate}')
        ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
        ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
        ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head}')
        ->leftJoin('sp.person', 'person')->addSelect('PARTIAL person.{id, firstname, lastname, birthdate}')
        ->leftJoin('p.createdBy', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
        ->leftJoin('p.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}');
    }

    /**
     * Filtre la recherche.
     *
     * @param PaymentSearch|SupportPaymentSearch $search
     */
    protected function filter(Querybuilder $query, $search): QueryBuilder
    {
        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        // if ($search->getId()) {
        //     $query->andWhere('p.id = :id')
        //         ->setParameter('id', $search->getId());
        // }

        if ($search->getType()) {
            $query->andWhere('p.type IN (:type)')
                ->setParameter('type', $search->getType());
        }

        if (in_array($search->getDateType(), [1, 3], true)) {
            if (1 === $search->getDateType()) {
                $dateType = 'paymentDate';
            } else {
                $dateType = 'createdAt';
            }
            if ($search->getStart()) {
                $query->andWhere('p.'.$dateType.' >= :start')
                ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('p.'.$dateType.' <= :end')
                ->setParameter('end', $search->getEnd());
            }
        } elseif (2 === $search->getDateType()) {
            if ($search->getStart()) {
                $query->andWhere('p.endDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('p.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search instanceof PaymentSearch) {
            if ($search->getFullname()) {
                $query->andWhere("CONCAT(person.lastname,' ' ,person.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
            }

            $query = $this->addOrganizationFilters($query, $search);
        }

        return $query;
    }

    /**
     * Return all payments of group support.
     */
    public function findPaymentsOfSupportQuery(int $supportGroupId, SupportPaymentSearch $search): Query
    {
        $query = $this->createQueryBuilder('p')->select('p')
            ->andWhere('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($search->getType()) {
            $query->andWhere('p.type IN (:type)')
                ->setParameter('type', $search->getType());
        }
        if ($search->getStart()) {
            $query->andWhere('p.startDate >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $query->andWhere('p.startDate <= :end')
                ->setParameter('end', $search->getEnd());
        }

        $query = $query->orderBy('p.createdAt', 'DESC');

        return $query->getQuery();
    }

    /**
     * Compte le nombre de payments financières.
     */
    public function countPayments(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('p')->select('COUNT(p.id)');

        if ($criteria) {
            $query = $query->leftJoin('p.supportGroup', 'sg');

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
                    $query = $query->andWhere('p.createdAt >= :startDate')
                        ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere('p.createdAt <= :endDate')
                        ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $query = $query->andWhere('p.createdBy = :createdBy')
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
        return $this->createQueryBuilder('p')->select('SUM(p.stillToPayAmt)')
            ->andWhere('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportId)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
