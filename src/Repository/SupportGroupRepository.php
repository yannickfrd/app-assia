<?php

namespace App\Repository;

use App\Entity\PeopleGroup;
use App\Entity\SupportGroup;
use App\Entity\User;
use App\Form\Model\AvdlSupportSearch;
use App\Form\Model\HotelSupportSearch;
use App\Form\Model\SupportGroupSearch;
use App\Form\Model\SupportsByUserSearch;
use App\Form\Model\SupportsInMonthSearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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
        $query = $this->getsupportQuery()
        ->leftJoin('sg.createdBy', 'user')->addSelect('PARTIAL user.{id, firstname, lastname}')
        ->leftJoin('sg.updatedBy', 'user2')->addSelect('PARTIAL user2.{id, firstname, lastname}')
        ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname}')
        ->leftJoin('sg.referent2', 'ref2')->addSelect('PARTIAL ref2.{id, firstname, lastname}')
        ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
        ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')
        ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name, logoPath}')

        // if ($service->getId() == Service::SERVICE_AVDL_ID) {
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')
        // }
        // if ($service->getId() == Service::SERVICE_PASH_ID) {
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
        // }

        // if ($supportGroup->getDevice()->getAccommodation() == Choices::YES) {
            ->leftJoin('sg.accommodationGroups', 'ag')->addSelect('ag')
            ->leftJoin('ag.accommodation', 'a')->addSelect('PARTIAL a.{id, name, address, city, zipcode}')
            ->leftJoin('ag.accommodationPeople', 'ap')->addSelect('ap')
            ->leftJoin('ap.supportPerson', 'sp2')->addSelect('sp2');
        // }

        return $query->andWhere('sg.id = :id')
            ->setParameter('id', $id)

            ->addOrderBy('sp.head', 'DESC')
            ->addOrderBy('p.birthdate', 'ASC')

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    protected function getsupportQuery()
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, preAdmission, justice}')
            ->leftJoin('sg.subService ', 'ss')->addSelect('PARTIAL ss.{id, name, email}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name, coefficient, accommodation, contribution, contributionType, contributionRate}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, usename, birthdate, gender, phone1, email}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')

            ->addOrderBy('sp.head', 'DESC')
            ->addOrderBy('p.birthdate', 'ASC');
    }

    /**
     * Donne tous les suivis sociaux.
     */
    public function findAllSupportsQuery(SupportGroupSearch $search): Query
    {
        $query = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            // ->leftJoin('sg.accommodationGroups', 'ag')->addSelect('PARTIAL ag.{id, accommodation}')
            // ->leftJoin('ag.accommodation', 'a')->addSelect('PARTIAL a.{id, name, address, city}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, usename, birthdate}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}');

        $query = $this->filter($query, $search);

        $query->andWhere('sp.head = TRUE');

        return $query->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne tous les suivis sociaux AVDL.
     */
    public function findAllAvdlSupportsQuery(AvdlSupportSearch $search, int $serviceId): Query
    {
        $query = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, usename, birthdate}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')

            ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
            ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')

            ->where('sg.service = :service')
            ->setParameter('service', $serviceId)
            ->andWhere('sp.head = TRUE');

        $query = $this->filter($query, $search);

        if (AvdlSupportSearch::DIAG == $search->getDiagOrSupport()) {
            $query->andWhere('avdl.diagStartDate IS NOT NULL');
        }
        if (AvdlSupportSearch::SUPPORT == $search->getDiagOrSupport()) {
            $query->andWhere('avdl.supportStartDate IS NOT NULL');
        }
        if ($search->getSupportType()) {
            $query->andWhere('avdl.supportType IN (:supportType)')
            ->setParameter('supportType', $search->getSupportType());
        }

        return $query->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne tous les suivis sociaux PASH.
     */
    public function findAllHotelSupportsQuery(HotelSupportSearch $search, int $serviceId): Query
    {
        $query = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
            ->leftJoin('sg.accommodationGroups', 'ag')->addSelect('PARTIAL ag.{id, accommodation}')
            ->leftJoin('ag.accommodation', 'a')->addSelect('PARTIAL a.{id, name}')

            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, usename, birthdate}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')

            ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
            ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')

            ->where('sg.service = :service')
            ->setParameter('service', $serviceId)
            ->andWhere('sp.head = TRUE');

        $query = $this->filter($query, $search);

        if ($search->getHotels()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getHotels() as $hotel) {
                $orX->add($expr->eq('ag.accommodation', $hotel));
            }
            $query->andWhere($orX);
        }

        if ($search->getLevelSupport()) {
            $query->andWhere('hs.levelSupport IN (:levelSupport)')
            ->setParameter('levelSupport', $search->getLevelSupport());
        }

        return $query->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne les suivis.
     *
     * @return mixed
     */
    public function getSupports(SupportGroupSearch $search)
    {
        $query = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id,name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('p')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('g')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, fullname}')
            ->andWhere('sp.head = TRUE');

        $query = $this->filter($query, $search);

        return $query->orderBy('sg.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Filtre.
     */
    protected function filter(QueryBuilder $query, $search): QueryBuilder
    {
        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }
        if ($search->getFullname()) {
            $query->andWhere("CONCAT(p.lastname, ' ', p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }

        if ($search->getFamilyTypologies()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getFamilyTypologies() as $typology) {
                $orX->add($expr->eq('g.familyTypology', $typology));
            }
            $query->andWhere($orX);
        }

        if ($search->getStatus()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getStatus() as $status) {
                $orX->add($expr->eq('sg.status', $status));
            }
            $query->andWhere($orX);
        }

        $supportDates = $search->getSupportDates();

        if (1 == $supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sg.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sg.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 == $supportDates) {
            if ($search->getStart()) {
                if ($search->getStart()) {
                    $query->andWhere('sg.endDate >= :start')
                        ->setParameter('start', $search->getStart());
                }
                if ($search->getEnd()) {
                    $query->andWhere('sg.endDate <= :end')
                        ->setParameter('end', $search->getEnd());
                }
            }
        }
        if (!$supportDates || 3 == $supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sg.endDate >= :start OR sg.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sg.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
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

        return $query;
    }

    /**
     * Donne tous les suivis sociaux de l'utilisateur.
     */
    public function findAllSupportsFromUser(User $user, $maxResults = null)
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
     *
     * @return mixed
     */
    public function findSupportsBetween(\Datetime $start, \Datetime $end, SupportsInMonthSearch $search = null)
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

    public function findSupportsForDashboard(SupportsByUserSearch $search)
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
     */
    public function findSupportsOfPeopleGroup(PeopleGroup $peopleGroup)
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
     * Donne le dernier suivi social auquel l'utilisateur peur avoir accès.
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
     * Donne le dernier suivi social auquel l'utilisateur peur avoir accès.
     */
    public function findLastSupport(SupportGroup $supportGroup): ?SupportGroup
    {
        $query = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.evaluationsGroup', 'eg')->addSelect('eg')
            ->leftJoin('sg.notes', 'n')->addSelect('n')
            ->leftJoin('sg.documents', 'd')->addSelect('d')

            ->where('sg.peopleGroup = :peopleGroup')
            ->setParameter('peopleGroup', $supportGroup->getPeopleGroup());

        if ($supportGroup->getId()) {
            $query->andWhere('sg.id != :id')
            ->setParameter('id', $supportGroup->getId());
        }

        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        return $query->orderBy('sg.updatedAt', 'DESC')

            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countSupports(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('sg')->select('COUNT(sg.id)');

        if ($criteria) {
            $dateFilter = $criteria['filterDateBy'] ?? 'createdAt';

            foreach ($criteria as $key => $value) {
                if ('service' == $key) {
                    $query = $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' == $key) {
                    $query = $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' == $key) {
                    $query = $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('user' == $key) {
                    $query = $query->andWhere('sg.referent = :user')
                        ->setParameter('user', $value);
                }
                if ('status' == $key) {
                    $query = $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
                if ('startDate' == $key) {
                    $query = $query->andWhere("sg.$dateFilter >= :startDate")
                            ->setParameter('startDate', $value);
                }
                if ('endDate' == $key) {
                    $query = $query->andWhere("sg.$dateFilter <= :endDate")
                            ->setParameter('endDate', $value);
                }
                if ('siaoRequest' == $key) {
                    $query = $query->leftJoin('sg.evaluationsGroup', 'e')
                        ->leftJoin('e.evalHousingGroup', 'ehg')

                        ->andWhere('ehg.siaoRequest = :siaoRequest')
                        ->setParameter('siaoRequest', $value);
                }
                if ('socialHousingRequest' == $key) {
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

    public function avgTimeSupport(array $criteria = null)
    {
        $query = $this->createQueryBuilder('sg');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('service' == $key) {
                    $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' == $key) {
                    $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' == $key) {
                    $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('status' == $key) {
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

    public function avgSupportsByUser(array $criteria = null)
    {
        $query = $this->createQueryBuilder('sg')->select('count(sg.referent)')
            ->where('sg.referent IS NOT NULL');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('service' == $key) {
                    $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('subService' == $key) {
                    $query->andWhere('sg.subService = :subService')
                        ->setParameter('subService', $value);
                }
                if ('device' == $key) {
                    $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
                if ('status' == $key) {
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
