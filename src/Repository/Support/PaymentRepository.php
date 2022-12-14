<?php

namespace App\Repository\Support;

use App\Entity\Organization\User;
use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\PaymentSearch;
use App\Form\Model\Support\SupportPaymentSearch;
use App\Repository\Traits\QueryTrait;
use App\Service\DoctrineTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    use DoctrineTrait;
    use QueryTrait;

    /** @var User */
    private $user;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, Payment::class);

        $this->user = $security->getUser();
    }

    /**
     * Donne un paiement.
     */
    public function findPayment(int $id, bool $deleted = false): ?Payment
    {
        if ($deleted) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        return $this->createQueryBuilder('p')->select('p')
            ->leftJoin('p.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, status, startDate, endDate, address, city, zipcode}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1, contribution, contributionType, contributionRate, city, address}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name, email, phone1}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name, logoPath}')
            ->leftJoin('pole.organization', 'o')->addSelect('PARTIAL o.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head, status}')
            ->leftJoin('sp.person', 'person')->addSelect('PARTIAL person.{id, firstname, lastname, birthdate, gender, email}')

            ->where("p.id = $id")

            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Donne toutes les participations financi??res ?? afficher.
     */
    public function findPaymentsQuery(?PaymentSearch $search = null): Query
    {
        $qb = $this->getPaymentQuery()
            ->andWhere('sp.head = TRUE');

        if ($search) {
            $qb = $this->filter($qb, $search);
        }

        return $qb
            ->orderBy('p.startDate', 'DESC')
            ->getQuery()
        ;
    }

    /**
     * Trouve tous les RDV entre 2 dates.
     *
     * @return Payment[]
     */
    public function findPaymentsBetween(\DateTime $start, \DateTime $end, array $supportsId): array
    {
        $qb = $this->createQueryBuilder('p')->select('p')
            ->leftJoin('p.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')

            ->andWhere('p.supportGroup IN (:supportsId)')
            ->setParameter('supportsId', $supportsId)

            ->andWhere('p.startDate >= :start')
            ->setParameter('start', $start)
            ->andWhere('p.startDate <= :end')
            ->setParameter('end', $end);

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Donne toutes les participations financi??res ?? exporter.
     *
     * @param PaymentSearch|SupportPaymentSearch $search
     *
     * @return Payment[]
     */
    public function findPaymentsToExport($search, SupportGroup $supportGroup = null): array
    {
        $qb = $this->getPaymentQuery()
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, siSiaoId}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}');

        if ($supportGroup) {
            $qb->where('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);
        }

        $qb = $this->filter($qb, $search);

        return $qb
            ->orderBy('p.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Donne toutes les PAF h??tel ?? exporter pour DELTA.
     *
     * @param PaymentSearch|SupportPaymentSearch $search
     *
     * @return Payment[]
     */
    public function findHotelContributionsToExport($search, SupportGroup $supportGroup = null): array
    {
        $qb = $this->getPaymentQuery()
            ->leftJoin('sg.originRequest', 'or')->addSelect('PARTIAL or.{id}')
            ->leftJoin('or.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, siSiaoId}');

        if ($supportGroup) {
            $qb->where('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);
        }

        $qb = $this->filter($qb, $search);

        return $qb
            ->orderBy('p.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Donne les paiements pour les indicateurs stats.
     *
     * @return Payment[]
     */
    public function findPaymentsForIndicators(PaymentSearch $search = null): array
    {
        $qb = $this->createQueryBuilder('p')->select('p')
            ->leftJoin('p.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, service, device}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, role, head, person}')
            ->leftJoin('sp.person', 'person')->addSelect('PARTIAL person.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}');

        if ($search) {
            $qb = $this->filter($qb, $search);
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Donne la requ??te.
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
    protected function filter(Querybuilder $qb, $search): QueryBuilder
    {
        if (!$this->user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->user->getServices());
        }

        // if ($search->getId()) {
        //     $qb->andWhere('p.id = :id')
        //         ->setParameter('id', $search->getId());
        // }

        if ($search->getType()) {
            $qb->andWhere('p.type IN (:type)')
                ->setParameter('type', $search->getType());
        }

        if (in_array($search->getDateType(), [1, 3], true)) {
            if (1 === $search->getDateType()) {
                $dateType = 'paymentDate';
            } else {
                $dateType = 'createdAt';
            }
            if ($search->getStart()) {
                $qb->andWhere('p.'.$dateType.' >= :start')
                ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $qb->andWhere('p.'.$dateType.' <= :end')
                ->setParameter('end', $search->getEnd());
            }
        } elseif (2 === $search->getDateType()) {
            if ($search->getStart()) {
                $qb->andWhere('p.endDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $qb->andWhere('p.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search instanceof PaymentSearch) {
            if ($search->getFullname()) {
                $qb->andWhere("CONCAT(person.lastname,' ' ,person.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
            }

            $qb = $this->addOrganizationFilters($qb, $search);
        }

        return $qb;
    }

    /**
     * Return all payments of group support.
     */
    public function findPaymentsOfSupportQuery(SupportGroup $supportGroup, SupportPaymentSearch $search = null): Query
    {
        $qb = $this->createQueryBuilder('p')
            ->andWhere('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);

        if ($search->getDeleted() && $this->user->hasRole('ROLE_SUPER_ADMIN')) {
            $this->disableFilter($this->_em, 'softdeleteable');
            $qb->andWhere('p.deletedAt IS NOT NULL');
        }
        if ($search->getType()) {
            $qb->andWhere('p.type IN (:type)')
                ->setParameter('type', $search->getType());
        }
        if ($search->getStart()) {
            $qb->andWhere('p.startDate >= :start')
                ->setParameter('start', $search->getStart());
        }
        if ($search->getEnd()) {
            $qb->andWhere('p.startDate <= :end')
                ->setParameter('end', $search->getEnd());
        }

        return $qb
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
        ;
    }

    /**
     * @return Payment[]
     */
    public function findPaymentsOfSupport(SupportGroup $supportGroup): array
    {
        return $this->findPaymentsOfSupportQuery($supportGroup)
            ->getResult()
        ;
    }

    public function findPaymentsOfSupportOrderedByStartDate(SupportGroup $supportGroup): array
    {
        return $this->createQueryBuilder('p')

            ->andWhere('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)
            ->andWhere('p.startDate <= :now')
            ->setParameter('now', new \DateTime())

            ->orderBy('p.startDate', 'DESC')
            ->getQuery()

            ->getResult()
        ;
    }

    /**
     * Return all payments of group support.
     */
    public function findAllPaymentsOfSupport(int $supportGroupId, bool $soft = false): ?array
    {
        $query = $this->createQueryBuilder('p')->select('p')
            ->andWhere('p.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId)
        ;

        if ($soft) {
            $query->andWhere('p.deletedAt IS NULL');
        }

        return $query
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Compte le nombre de payments financi??res.
     */
    public function countPayments(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('p')->select('COUNT(p.id)');

        if ($criteria) {
            $qb->leftJoin('p.supportGroup', 'sg');

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
                    $qb->andWhere('p.createdAt >= :startDate')
                        ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere('p.createdAt <= :endDate')
                        ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $qb->andWhere('p.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult()
        ;
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
            ->getSingleScalarResult()
        ;
    }
}
