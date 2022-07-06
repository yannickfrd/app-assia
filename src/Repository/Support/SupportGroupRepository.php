<?php

namespace App\Repository\Support;

use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportsByUserSearch;
use App\Form\Model\Support\SupportsInMonthSearch;
use App\Repository\Traits\QueryTrait;
use App\Service\DoctrineTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method SupportGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportGroup[]    findAll()
 * @method SupportGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportGroupRepository extends ServiceEntityRepository
{
    use DoctrineTrait;
    use QueryTrait;

    /** @var User */
    private $user;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, SupportGroup::class);

        $this->user = $security->getUser();
    }

    /**
     * @return float|int|mixed|string
     */
    public function findEndDate()
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id}')
            ->leftJoin('sg.notes', 'nt')->addSelect('PARTIAL nt.{id}')
            ->leftJoin('sg.documents', 'doc')->addSelect('PARTIAL doc.{id}')
            ->leftJoin('sg.rdvs', 'rdv')->addSelect('PARTIAL rdv.{id}')
            ->leftJoin('sg.payments', 'pmt')->addSelect('PARTIAL pmt.{id}')

            ->where('sg.endDate IS NOT NULL')
            ->andWhere('sg.status = '.SupportGroup::STATUS_ENDED)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    /**
     * Donne le suivi social avec le groupe et les personnes rattachées.
     */
    public function findSupportById(int $id, bool $deleted = false): ?SupportGroup
    {
        if ($deleted) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        $qb = $this->getSupportQuery();

        return $qb
            ->andWhere('sg.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne le suivi social complet avec le groupe et les personnes rattachées.
     */
    public function findFullSupportById(int $id, bool $deleted = false): ?SupportGroup
    {
        if ($deleted) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        return $this->getSupportQuery()
        ->leftJoin('sg.updatedBy', 'user2')->addSelect('PARTIAL user2.{id, firstname, lastname}')
        ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
        ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')
        ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name, logoPath}')
        ->leftJoin('pole.organization', 'pole_orga')->addSelect('PARTIAL pole_orga.{id, name}')

        // if ($service->getType() === Service::SERVICE_TYPE_AVDL) {
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')
        // }
        // if ($service->getType() === Service::SERVICE_TYPE_HOTEL) {
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
        // }

        // if ($supportGroup->getDevice()->getPlace() === Choices::YES) {
            ->leftJoin('sg.placeGroups', 'pg')->addSelect('pg')
            ->leftJoin('pg.place', 'pl')->addSelect('PARTIAL pl.{id, name, address, city, zipcode}')
            ->leftJoin('pg.placePeople', 'pp')->addSelect('pp')
            ->leftJoin('pp.supportPerson', 'sp2')->addSelect('sp2')
        // }

            ->andWhere('sg.id = :id')
            ->setParameter('id', $id)

            ->addOrderBy('sp.status', 'ASC')
            ->addOrderBy('sp.head', 'DESC')
            ->addOrderBy('p.birthdate', 'ASC')

            ->getQuery()
//            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    protected function getSupportQuery(bool $withOrder = true): QueryBuilder
    {
        $qb = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.createdBy', 'user')->addSelect('PARTIAL user.{id, firstname, lastname}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname}')
            ->leftJoin('sg.referent2', 'ref2')->addSelect('PARTIAL ref2.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('s')
            ->leftJoin('sg.subService ', 'ss')->addSelect('PARTIAL ss.{id, name, email}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name, code, coefficient, place, contribution, contributionType, contributionRate}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, usename, birthdate, gender, phone1, email}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople, siSiaoId}');

        if ($withOrder) {
            $qb->addOrderBy('sp.head', 'DESC')
                ->addOrderBy('p.birthdate', 'ASC');
        }

        return $qb;
    }

    /**
     * Donne tous les suivis sociaux de l'utilisateur.
     *
     * @return SupportGroup[]
     */
    public function findSupportsOfUser(User $user, $maxResults = null): array
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 'sv')->addSelect('PARTIAL sv.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, usename}')
            ->leftJoin('sg.tasks', 't')->addSelect('PARTIAL t.{id, title, status, end}')

            ->andWhere('sg.referent = :referent')
            ->setParameter('referent', $user)
            ->andWhere('sg.status ='.SupportGroup::STATUS_IN_PROGRESS)
            ->andWhere('sp.head = TRUE')

            ->orderBy('p.lastname', 'ASC')

            ->setMaxResults($maxResults)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les suivis sociaux de l'utilisateur.
     */
    public function getSupportsOfUser(User $user, ?SupportGroup $supportGroup = null): array
    {
        $qb = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->where('sp.head = TRUE')
            ->andWhere('sg.status = :status')
            ->setParameter('status', SupportGroup::STATUS_IN_PROGRESS)
            ->andWhere('sg.referent = :referent')
            ->setParameter('referent', $user);

        if ($supportGroup) {
            $qb->orWhere('sg.id = :id')
                ->setParameter('id', $supportGroup->getId());
        }

        return $qb
            ->orderBy('p.lastname', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    /**
     * Trouve tous les suivis entre 2 dates.
     */
    public function findSupportsBetween(\DateTime $start, \DateTime $end, SupportsInMonthSearch $search = null): Query
    {
        $qb = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')

            ->andWhere('sg.endDate >= :start OR sg.endDate IS NULL')
            ->setParameter('start', $start)
            ->andWhere('sg.startDate <= :end')
            ->setParameter('end', $end)
            ->andWhere('sp.head = TRUE');

        if (!$this->user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('s.id IN (:services)')
                ->setParameter('services', $this->user->getServices());
        }

        $qb = $this->addOrganizationFilters($qb, $search);

        return $qb
            ->orderBy('sg.startDate', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne les suivis pour le tableau de bord.
     *
     * @return SupportGroup[]
     */
    public function findSupportsForDashboard(SupportsByUserSearch $search): array
    {
        $qb = $this->createQueryBuilder('sg')->select('PARTIAL sg.{id, status, startDate, referent, service, device, coefficient}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, coefficient}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}');

        if (!$this->user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->where('s.id IN (:services)')
                ->setParameter('services', $this->user->getServices());
        }

        $qb = $this->addOrganizationFilters($qb, $search);

        return $qb
            ->andWhere('sg.status = :status')
            ->setParameter('status', 2)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne les suivis sociaux du ménage.
     *
     * @return SupportGroup[]
     */
    public function findSupportsOfPeopleGroup(PeopleGroup $peopleGroup): array
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname, email, phone1}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')

            ->where('sg.peopleGroup = :peopleGroup')
            ->setParameter('peopleGroup', $peopleGroup)

            ->orderBy('sg.startDate', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Trouve le dernier suivi social en fonction de l'utilisateur.
     */
    public function findLastSupport(SupportGroup $supportGroup): ?SupportGroup
    {
        $qb = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id}')

            ->andWhere('sg.peopleGroup = :peopleGroup')
            ->setParameter('peopleGroup', $supportGroup->getPeopleGroup())
            ->andWhere('sg.id != :supportGroup')
            ->setParameter('supportGroup', $supportGroup->getId());

        if (!$this->user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->user->getServices());
        }

        return $qb
            ->orderBy('sg.updatedAt', 'DESC')
            ->setMaxResults(1)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    public function countSupports(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('sg')->select('COUNT(sg.id)');

        if ($criteria) {
            $dateFilter = $criteria['filterDateBy'] ?? 'createdAt';

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
                if ('user' === $key) {
                    $qb->andWhere('sg.referent = :user')
                        ->setParameter('user', $value);
                }
                if ('startDate' === $key) {
                    $qb->andWhere("sg.$dateFilter >= :startDate")
                        ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere("sg.$dateFilter <= :endDate")
                        ->setParameter('endDate', $value);
                }
                if ('siaoRequest' === $key) {
                    $qb->leftJoin('sg.evaluationsGroup', 'e')
                        ->leftJoin('e.evalHousingGroup', 'ehg')

                        ->andWhere('ehg.siaoRequest = :siaoRequest')
                        ->setParameter('siaoRequest', $value);
                }
                if ('socialHousingRequest' === $key) {
                    $qb->leftJoin('sg.evaluationsGroup', 'e')
                        ->leftJoin('e.evalHousingGroup', 'ehg')

                        ->andWhere('ehg.socialHousingRequest = :socialHousingRequest')
                        ->setParameter('socialHousingRequest', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Donne la durée moyenne d'un suivi.
     */
    public function avgTimeSupport(array $criteria = null): ?float
    {
        $qb = $this->createQueryBuilder('sg');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $qb->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' === $key) {
                    $qb->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' === $key) {
                    $qb->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('status' === $key) {
                    $qb->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        $today = (new \DateTime())->format('Y-m-d');
        // $expr = $qb->expr();
        // $diff = $expr->diff('sg.startDate', $today);
        // $avg = $expr->avg($diff);
        // $qb->select($avg);
        $qb->select('avg(date_diff(:today, sg.startDate)) as avgTimeSupport')
            ->setParameter(':today', $today);

        $result = $qb
            ->getQuery()
            ->getSingleScalarResult();

        return round($result);
    }

    /**
     * Donne le nombre moyen de suivis par utilisateur.
     */
    public function avgSupportsByUser(array $criteria = null): ?float
    {
        $qb = $this->createQueryBuilder('sg')->select('count(sg.referent)')
            ->where('sg.referent IS NOT NULL');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $qb->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' === $key) {
                    $qb->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' === $key) {
                    $qb->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('status' === $key) {
                    $qb->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        $result = $qb
            ->addGroupBy('sg.referent')
            ->getQuery()
            ->getScalarResult();

        $sum = 0;
        $i = 0;
        foreach ($result as $value) {
            $sum = $sum + (int) $value['1'];
            ++$i;
        }

        return $i > 0 ? round($sum / $i, 1) : null;
    }

    /**
     * @return float|int|mixed|string|null
     */
    public function countSupportGroupElements(?\DateTimeInterface $limitDate = null)
    {
        return $this->createQueryBuilder('sg')->select('sg.id')
            ->leftJoin('sg.notes', 'n')->addSelect('count(DISTINCT n.id) as nb_notes')
            ->leftJoin('sg.documents', 'd')->addSelect('count(DISTINCT d.id) as nb_documents')
            ->leftJoin('sg.payments', 'p')->addSelect('count(DISTINCT p.id) as nb_payments')
            ->leftJoin('sg.rdvs', 'r')->addSelect('count(DISTINCT r.id) as nb_rdvs')
            ->leftJoin('sg.tasks', 't')->addSelect('count(DISTINCT t.id) as nb_tasks')

            ->groupBy('sg.id')

            ->where('sg.endDate IS NOT NULL')
            ->andWhere('sg.endDate <= :limitDate')
            ->setParameter('limitDate', $limitDate ?? new \DateTime())

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    /**
     * @return SupportGroup[]
     */
    public function findEndedSupports(?\DateTimeInterface $limitDate = null): array
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')

            ->leftJoin('sg.peopleGroup', 'pg')->addSelect('pg')
            ->leftJoin('pg.supports', 'sg2')->addSelect('sg2')
            ->leftJoin('pg.rolePeople', 'rp')->addSelect('rp')
            ->leftJoin('rp.person', 'p')->addSelect('p')
            ->leftJoin('p.rolesPerson', 'rp2')->addSelect('rp2')
            ->leftJoin('rp2.person', 'p2')->addSelect('p2')
            ->leftJoin('p.supports', 'sp2')->addSelect('sp2')

            ->leftJoin('sg.originRequest', 's_gor')->addSelect('s_gor')
            ->leftJoin('sg.avdl', 'sg_avdl')->addSelect('sg_avdl')
            ->leftJoin('sg.hotelSupport', 'sg_hs')->addSelect('sg_hs')
            ->leftJoin('sg.evalInitGroup', 'sg_ieg')->addSelect('sg_ieg')
            ->leftJoin('sp.evalInitPerson', 'sp_ieg')->addSelect('sp_ieg')

            ->leftJoin('sg2.originRequest', 'sg2_or')->addSelect('sg2_or')
            ->leftJoin('sg2.avdl', 'sg2_avdl')->addSelect('sg2_avdl')
            ->leftJoin('sg2.hotelSupport', 'sg2_hs')->addSelect('sg2_hs')
            ->leftJoin('sg2.evalInitGroup', 'sg2_ieg')->addSelect('sg2_ieg')
            ->leftJoin('sp2.evalInitPerson', 'sp2_iep')->addSelect('sp2_iep')

            ->where('sg.status IN (:status)')
            ->setParameter('status', [
                SupportGroup::STATUS_ENDED,
                SupportGroup::STATUS_PRE_ADD_FAILED,
                SupportGroup::STATUS_SUSPENDED,
                SupportGroup::STATUS_OTHER,
            ])

            ->andWhere('sg.updatedAt <= :limitDate')
            ->setParameter('limitDate', $limitDate ?? new \DateTime())

            ->getQuery()
            ->getResult()
        ;
    }
}
