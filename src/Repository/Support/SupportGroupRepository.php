<?php

namespace App\Repository\Support;

use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\SupportsByUserSearch;
use App\Form\Model\Support\SupportsInMonthSearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupportGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportGroup[]    findAll()
 * @method SupportGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportGroupRepository extends ServiceEntityRepository
{
    private $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, SupportGroup::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Donne le suivi social avec le groupe et les personnes rattachées.
     */
    public function findSupportById(int $id): ?SupportGroup
    {
        $query = $this->getsupportQuery();

        return $query->andWhere('sg.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne le suivi social complet avec le groupe et les personnes rattachées.
     */
    public function findFullSupportById(int $id): ?SupportGroup
    {
        return $this->getsupportQuery()
        ->leftJoin('sg.updatedBy', 'user2')->addSelect('PARTIAL user2.{id, firstname, lastname}')
        ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
        ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')
        ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name, logoPath}')

        // if ($service->getId() === Service::SERVICE_AVDL_ID) {
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')
        // }
        // if ($service->getId() === Service::SERVICE_PASH_ID) {
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
        // }

        // if ($supportGroup->getDevice()->getAccommodation() === Choices::YES) {
            ->leftJoin('sg.accommodationGroups', 'ag')->addSelect('ag')
            ->leftJoin('ag.accommodation', 'a')->addSelect('PARTIAL a.{id, name, address, city, zipcode}')
            ->leftJoin('ag.accommodationPeople', 'ap')->addSelect('ap')
            ->leftJoin('ap.supportPerson', 'sp2')->addSelect('sp2')
        // }

            ->andWhere('sg.id = :id')
            ->setParameter('id', $id)

            ->addOrderBy('sp.status', 'ASC')
            ->addOrderBy('sp.head', 'DESC')
            ->addOrderBy('p.birthdate', 'ASC')

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    protected function getsupportQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.createdBy', 'user')->addSelect('PARTIAL user.{id, firstname, lastname}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname}')
            ->leftJoin('sg.referent2', 'ref2')->addSelect('PARTIAL ref2.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, preAdmission, justice, coefficient}')
            ->leftJoin('sg.subService ', 'ss')->addSelect('PARTIAL ss.{id, name, email}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name, coefficient, accommodation, contribution, contributionType, contributionRate}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, usename, birthdate, gender, phone1, email}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')

            ->addOrderBy('sp.head', 'DESC')
            ->addOrderBy('p.birthdate', 'ASC');
    }

    /**
     * Donne tous les suivis sociaux de l'utilisateur.
     *
     * @return SupportGroup[]|null
     */
    public function findSupportsOfUser(User $user, $maxResults = null): ?array
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 'sv')->addSelect('PARTIAL sv.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, usename}')

            ->andWhere('sg.referent = :referent')
            ->setParameter('referent', $user)
            ->andWhere('sg.status ='.SupportGroup::STATUS_IN_PROGRESS)
            ->andWhere('sp.head = TRUE')

            ->orderBy('p.lastname', 'ASC')

            ->setMaxResults($maxResults)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Trouve tous les suivis entre 2 dates.
     */
    public function findSupportsBetween(\Datetime $start, \Datetime $end, SupportsInMonthSearch $search = null): Query
    {
        $query = $this->createQueryBuilder('sg')->select('sg')
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

        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        if ($search->getReferents() && $search->getReferents()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

        if ($search->getServices() && $search->getServices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($search->getDevices() && $search->getDevices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        return $query->orderBy('sg.startDate', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne les suivis pour le tableau de bord.
     *
     * @return SupportGroup[]|null
     */
    public function findSupportsForDashboard(SupportsByUserSearch $search): ?array
    {
        $query = $this->createQueryBuilder('sg')->select('PARTIAL sg.{id, status, startDate, referent, service, device, coefficient}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}');

        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->where('s.id IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        if ($search->getServices() && $search->getServices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($search->getSubServices() && $search->getSubServices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getSubServices() as $subService) {
                $orX->add($expr->eq('sg.subService', $subService));
            }
            $query->andWhere($orX);
        }

        if ($search->getDevices() && $search->getDevices()->count() > 0) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        $query = $query->andWhere('sg.status = :status')
            ->setParameter('status', 2)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();

        return $query;
    }

    /**
     * Donne les suivis sociaux du ménage.
     *
     * @return SupportGroup[]|null
     */
    public function findSupportsOfPeopleGroup(PeopleGroup $peopleGroup): ?array
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname, email, phone1}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')

            ->where('sg.peopleGroup = :peopleGroup')
            ->setParameter('peopleGroup', $peopleGroup)

            ->orderBy('sg.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne le dernier suivi social auquel l'utilisateur peut avoir accès.
     */
    public function countSupportOfPeopleGroup(PeopleGroup $peopleGroup): int
    {
        $query = $this->createQueryBuilder('sg')->select('count(sg.id)')

            ->where('sg.peopleGroup = :peopleGroup')
            ->setParameter('peopleGroup', $peopleGroup);

        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve le dernier suivi social en fonction de l'utilisateur.
     */
    public function findLastSupport(SupportGroup $supportGroup): ?SupportGroup
    {
        $query = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id}')

            ->andWhere('sg.peopleGroup = :peopleGroup')
            ->setParameter('peopleGroup', $supportGroup->getPeopleGroup())
            ->andWhere('sg.id != :supportGroup')
            ->setParameter('supportGroup', $supportGroup);

        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        return $query
            ->orderBy('sg.updatedAt', 'DESC')
            ->setMaxResults(1)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();

        // $paginator = new Paginator($query);
        // foreach ($paginator->getIterator() as $supportGroup) {
        //     return $supportGroup;
        // }
    }

    public function countSupports(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('sg')->select('COUNT(sg.id)');

        if ($criteria) {
            $dateFilter = $criteria['filterDateBy'] ?? 'createdAt';

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
                if ('user' === $key) {
                    $query = $query->andWhere('sg.referent = :user')
                        ->setParameter('user', $value);
                }
                if ('status' === $key) {
                    $query = $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
                if ('startDate' === $key) {
                    $query = $query->andWhere("sg.$dateFilter >= :startDate")
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere("sg.$dateFilter <= :endDate")
                            ->setParameter('endDate', $value);
                }
                if ('siaoRequest' === $key) {
                    $query = $query->leftJoin('sg.evaluationsGroup', 'e')
                        ->leftJoin('e.evalHousingGroup', 'ehg')

                        ->andWhere('ehg.siaoRequest = :siaoRequest')
                        ->setParameter('siaoRequest', $value);
                }
                if ('socialHousingRequest' === $key) {
                    $query = $query->leftJoin('sg.evaluationsGroup', 'e')
                        ->leftJoin('e.evalHousingGroup', 'ehg')

                        ->andWhere('ehg.socialHousingRequest = :socialHousingRequest')
                        ->setParameter('socialHousingRequest', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Donne la durée moyenne d'un suivi.
     */
    public function avgTimeSupport(array $criteria = null): ?float
    {
        $query = $this->createQueryBuilder('sg');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' === $key) {
                    $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' === $key) {
                    $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('status' === $key) {
                    $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        $today = (new \DateTime())->format('Y-m-d');
        // $expr = $query->expr();
        // $diff = $expr->diff('sg.startDate', $today);
        // $avg = $expr->avg($diff);
        // $query = $query->select($avg);
        $query->select('avg(date_diff(:today, sg.startDate)) as avgTimeSupport')
            ->setParameter(':today', $today);

        $result = $query->getQuery()
            ->getSingleScalarResult();

        return round($result);
    }

    /**
     * Donne le nombre moyen de suivis par utilisateur.
     */
    public function avgSupportsByUser(array $criteria = null): ?float
    {
        $query = $this->createQueryBuilder('sg')->select('count(sg.referent)')
            ->where('sg.referent IS NOT NULL');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' === $key) {
                    $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' === $key) {
                    $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('status' === $key) {
                    $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        $query = $query->addGroupBy('sg.referent')
            ->getQuery();

        $result = $query->getScalarResult();

        $sum = 0;
        $i = 0;
        foreach ($result as $value) {
            $sum = $sum + (int) $value['1'];
            ++$i;
        }

        return  $i > 0 ? round($sum / $i, 1) : null;
    }
}
