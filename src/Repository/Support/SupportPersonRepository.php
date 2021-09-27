<?php

namespace App\Repository\Support;

use App\Entity\Organization\Service;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\Support\SupportPerson;
use App\Form\Model\Support\AvdlSupportSearch;
use App\Form\Model\Support\HotelSupportSearch;
use App\Form\Model\Support\SupportSearch;
use App\Repository\Traits\QueryTrait;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SupportPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportPerson[]    findAll()
 * @method SupportPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportPersonRepository extends ServiceEntityRepository
{
    use QueryTrait;

    private $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, SupportPerson::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Trouve les suivis sociaux.
     */
    public function findSupportsQuery(SupportSearch $search): Query
    {
        $query = $this->getSupportsQuery();

        if ($search) {
            $query = $this->filters($query, $search);
        }

        return $query->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Retourne toutes les suivis pour l'export.
     *
     * @return SupportPerson[]|null
     */
    public function findSupportsToExport(?SupportSearch $search = null): ?array
    {
        $query = $this->getSupportsQuery()
            ->leftJoin('sp.placesPerson', 'pp')->addSelect('pp')
            ->leftJoin('pp.placeGroup', 'pg')->addSelect('pg')
            ->leftJoin('pg.place', 'pl')->addSelect('pl');

        if ($search) {
            $query = $this->filters($query, $search);
        }

        return $query->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Retourne toutes les suivis d'un service pour l'export.
     *
     * @return SupportPerson[]|null
     */
    public function findSupportsOfServiceToExport($search = null, int $serviceType): ?array
    {
        $query = $this->getSupportsOfServiceQuery()
            ->leftJoin('sg.placeGroups', 'pg')->addSelect('PARTIAL pg.{id, place}')
            ->leftJoin('pg.place', 'pl')->addSelect('PARTIAL pl.{id, name}')

            ->where('s.type = :type')
            ->setParameter('type', $serviceType);

        if ($search) {
            $query = $this->filters($query, $search);
        }

        return $query->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Trouve les suivis sociaux AVDL.
     */
    public function findAvdlSupportsQuery(AvdlSupportSearch $search): Query
    {
        $query = $this->getSupportsQuery()
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')

            ->where('s.type = :type')
            ->setParameter('type', Service::SERVICE_TYPE_AVDL);

        $query = $this->filters($query, $search);

        if (AvdlSupportSearch::DIAG === $search->getDiagOrSupport()) {
            $query->andWhere('avdl.diagStartDate IS NOT NULL');
        }
        if (AvdlSupportSearch::SUPPORT === $search->getDiagOrSupport()) {
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
     * Trouve les suivis sociaux hôtel.
     */
    public function findHotelSupportsQuery(HotelSupportSearch $search): Query
    {
        $query = $this->getSupportsQuery()
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
            ->leftJoin('sg.placeGroups', 'pg')->addSelect('PARTIAL pg.{id, place}')
            ->leftJoin('pg.place', 'pl')->addSelect('PARTIAL pl.{id, name}')

            ->where('s.type = :type')
            ->setParameter('type', Service::SERVICE_TYPE_HOTEL);

        $query = $this->filters($query, $search);

        if ($search->getHotels()) {
            $query = $this->addOrWhere($query, 'pg.place', $search->getHotels());
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
     * Donne les suivis sociaux de la personne.
     *
     * @return SupportPerson[]|null
     */
    public function findSupportsOfPerson(Person $person): ?array
    {
        return $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.referent', 'ref')->addSelect('PARTIAL ref.{id, firstname, lastname, email, phone1}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')

            ->where('sp.person = :person')
            ->setParameter('person', $person)

            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les suivis pour l'export complet.
     *
     * @return SupportPerson[]|null
     */
    public function findSupportsFullToExport($search = null): ?array
    {
        $query = $this->getSupportsQuery()
            ->leftJoin('sp.placesPerson', 'pp')->addSelect('pp')
            ->leftJoin('pp.placeGroup', 'pg')->addSelect('pg')
            ->leftJoin('pg.place', 'pl')->addSelect('pl')

            ->leftJoin('g.referents', 'ref')->addSelect('PARTIAL ref.{id, name, type}')

            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')

            ->leftJoin('sp.evaluationsPerson', 'ep')->addSelect('PARTIAL ep.{id}')
            ->leftJoin('ep.initEvalPerson', 'initEvalPerson')->addSelect('initEvalPerson')
            ->leftJoin('ep.evalJusticePerson', 'evalJusticePerson')->addSelect('evalJusticePerson')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addSelect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'evalBudgetPerson')->addSelect('evalBudgetPerson')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addSelect('evalFamilyPerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addSelect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addSelect('evalSocialPerson')

            ->leftJoin('ep.evaluationGroup', 'eg')->addSelect('PARTIAL eg.{id}')
            ->leftJoin('eg.initEvalGroup', 'initEvalGroup')->addSelect('initEvalGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addSelect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addSelect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addSelect('evalHousingGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addSelect('evalSocialGroup');

        return $this->filters($query, $search)

            ->setMaxResults(9999)
            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Compte le nombre de suivis à exporter.
     */
    public function countSupportsToExport($search = null): int
    {
        $query = $this->getSupportsQuery()
            ->select('COUNT(sp.id)');

        return $this->filters($query, $search)

            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Trouve le dernier suivi social de la personne en fonction de l'utilisateur.
     */
    public function findLastSupport(SupportPerson $supportPerson): ?SupportPerson
    {
        $query = $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id}')

            ->andWhere('sp.person = :person')
            ->setParameter('person', $supportPerson->getPerson())
            ->andWhere('sp.id != :supportPerson')
            ->setParameter('supportPerson', $supportPerson);

        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        return $query
            ->orderBy('sp.updatedAt', 'DESC')
            ->setMaxResults(1)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne le Querybuilder d'un suivi.
     */
    protected function getSupportsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople, siSiaoId}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, type, coefficient}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
            ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}');
    }

    protected function getSupportsOfServiceQuery(): QueryBuilder
    {
        return $this->getSupportsQuery()
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs');
    }

    /**
     * Filtre la recherche.
     */
    protected function filters(QueryBuilder $query, $search): QueryBuilder
    {
        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        if ($search->getHead()) {
            $query->andWhere('sp.head = :head')
                ->setParameter('head', $search->getHead());
        }

        if ($search->getFullname()) {
            $date = \DateTime::createFromFormat('d/m/Y', $search->getFullname()) ?? false;
            $int = ((int) $search->getFullname());
            if ($date) {
                $query->andWhere('p.birthdate = :birthdate')
                    ->setParameter('birthdate', $date->format('Y-m-d'));
            } elseif ($int > 0) {
                $query->andWhere('g.siSiaoId = :id')
                    ->setParameter('id', $int);
            } else {
                $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                    ->setParameter('fullname', '%'.$search->getFullname().'%');
            }
        }

        $query = $this->addOrganizationFilters($query, $search);

        if ($search->getStatus()) {
            $query = $this->addOrWhere($query, 'sg.status', $search->getStatus());
        }

        $supportDates = $search->getSupportDates();

        if (1 === $supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sp.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sp.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 === $supportDates) {
            if ($search->getStart()) {
                if ($search->getStart()) {
                    $query->andWhere('sp.endDate >= :start')
                        ->setParameter('start', $search->getStart());
                }
                if ($search->getEnd()) {
                    $query->andWhere('sp.endDate <= :end')
                        ->setParameter('end', $search->getEnd());
                }
            }
        }
        if (3 === $supportDates || !$supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sp.endDate >= :start OR sp.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sp.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search->getFamilyTypologies()) {
            $query = $this->addOrWhere($query, 'g.familyTypology', $search->getFamilyTypologies());
        }

        return $query;
    }

    /**
     * Donne le nombre de suivis des personnes du groupe.
     */
    public function countSupportsOfPeople(PeopleGroup $peopleGroup): int
    {
        $query = $this->createQueryBuilder('sp')->select('count(sp.id)')
            ->leftJoin('sp.supportGroup', 'sg');

        $query = $this->addOrWhere($query, 'sp.person', $peopleGroup->getPeople());

        if (!$this->currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    public function countSupportPeople(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('sp')->select('COUNT(sp.id)')
            ->leftJoin('sp.supportGroup', 'sg');

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
                    $query->andWhere('sp.status = :status')
                        ->setParameter('status', $value);
                }
                if ('startDate' === $key) {
                    $query = $query->andWhere('sp.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere('sp.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
            }
        }

        return (int) $query->getQuery()
            ->getSingleScalarResult();
    }
}
