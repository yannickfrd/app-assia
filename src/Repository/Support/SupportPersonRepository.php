<?php

namespace App\Repository\Support;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Model\Support\AvdlSupportSearch;
use App\Form\Model\Support\HotelSupportSearch;
use App\Form\Model\Support\SupportSearch;
use App\Repository\Traits\QueryTrait;
use App\Service\DoctrineTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Security;

/**
 * @method SupportPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportPerson[]    findAll()
 * @method SupportPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportPersonRepository extends ServiceEntityRepository
{
    use QueryTrait;
    use DoctrineTrait;

    public const EXPORT_LIMIT = 15_000;

    /** @var User */
    private $user;

    public function __construct(ManagerRegistry $registry, Security $security)
    {
        parent::__construct($registry, SupportPerson::class);

        $this->user = $security->getUser();
    }

    public function findSupportPerson(int $id, bool $deleted = false): ?SupportPerson
    {
        if ($deleted) {
            $this->disableFilter($this->_em, 'softdeleteable');
        }

        return $this->createQueryBuilder('sp')
            ->leftJoin('sp.person', 'p')->addSelect('p')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.service', 's')->addSelect('s')
            ->leftJoin('sg.peopleGroup', 'pg')->addSelect('pg')
            ->leftJoin('sg.supportPeople', 'sp2')->addSelect('sp2')
            ->leftJoin('sp2.person', 'p2')->addSelect('p2')
            ->leftJoin('p2.rolesPerson', 'r')->addSelect('r')

            ->where('sp.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->getSingleResult()
        ;
    }

    /**
     * Trouve les suivis sociaux.
     */
    public function findSupportsQuery(SupportSearch $search): Query
    {
        $qb = $this->getSupportsQuery();

        if ($search) {
            $qb = $this->filters($qb, $search);
        }

        return $qb
            ->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Retourne toutes les suivis pour l'export.
     *
     * @return SupportPerson[]
     */
    public function findSupportsToExport(?SupportSearch $search = null): array
    {
        $qb = $this->getSupportsQuery()
            ->leftJoin('sp.placesPerson', 'pp')->addSelect('pp')
            ->leftJoin('pp.placeGroup', 'pg')->addSelect('pg')
            ->leftJoin('pg.place', 'pl')->addSelect('pl');

        if ($search) {
            $qb = $this->filters($qb, $search);
        }

        return $qb
            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Retourne toutes les suivis d'un service pour l'export.
     *
     * @return SupportPerson[]
     */
    public function findSupportsOfServiceToExport($search = null, int $serviceType): array
    {
        $qb = $this->getSupportsOfServiceQuery()
            ->leftJoin('sg.placeGroups', 'pg')->addSelect('PARTIAL pg.{id, place}')
            ->leftJoin('pg.place', 'pl')->addSelect('PARTIAL pl.{id, name}')

            ->where('s.type = :type')
            ->setParameter('type', $serviceType);

        if ($search) {
            $qb = $this->filters($qb, $search);
        }

        return $qb
            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Trouve les suivis sociaux AVDL.
     */
    public function findAvdlSupportsQuery(AvdlSupportSearch $search): Query
    {
        $qb = $this->getSupportsQuery()
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')

            ->where('s.type = :type')
            ->setParameter('type', Service::SERVICE_TYPE_AVDL);

        $qb = $this->filters($qb, $search);

        if (AvdlSupportSearch::DIAG === $search->getDiagOrSupport()) {
            $qb->andWhere('avdl.diagStartDate IS NOT NULL');
        }
        if (AvdlSupportSearch::SUPPORT === $search->getDiagOrSupport()) {
            $qb->andWhere('avdl.supportStartDate IS NOT NULL');
        }
        if ($search->getSupportType()) {
            $qb->andWhere('avdl.supportType IN (:supportType)')
            ->setParameter('supportType', $search->getSupportType());
        }

        return $qb
            ->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Trouve les suivis sociaux hôtel.
     */
    public function findHotelSupportsQuery(HotelSupportSearch $search): Query
    {
        $qb = $this->getSupportsQuery()
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
            ->leftJoin('sg.placeGroups', 'pg')->addSelect('PARTIAL pg.{id, place}')
            ->leftJoin('pg.place', 'pl')->addSelect('PARTIAL pl.{id, name}')

            ->where('s.type = :type')
            ->setParameter('type', Service::SERVICE_TYPE_HOTEL)
        ;

        $qb = $this->filters($qb, $search);

        if ($search->getHotels()) {
            $qb = $this->addOrWhere($qb, 'pg.place', $search->getHotels());
        }

        if ($search->getLevelSupport()) {
            $qb->andWhere('hs.levelSupport IN (:levelSupport)')
            ->setParameter('levelSupport', $search->getLevelSupport());
        }

        return $qb
            ->orderBy('sg.updatedAt', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
        ;
    }

    /**
     * Donne les suivis sociaux de la personne.
     *
     * @return SupportPerson[]
     */
    public function findSupportsOfPerson(Person $person): array
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
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les suivis pour l'export complet.
     *
     * @return SupportPerson[]
     */
    public function findSupportsFullToExport($search = null, $limit = 99_999): array
    {
        $qb = $this->getSupportsQuery()
            ->leftJoin('sp.placesPerson', 'pp')->addSelect('pp')
            ->leftJoin('pp.placeGroup', 'pg')->addSelect('PARTIAL pg.{id}')
            ->leftJoin('pg.place', 'pl')->addSelect('PARTIAL pl.{id, name}')

            ->leftJoin('g.referents', 'ref')->addSelect('PARTIAL ref.{id, name, type}')

            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')

            ->leftJoin('sp.evaluations', 'ep')->addSelect('PARTIAL ep.{id}')
            ->leftJoin('ep.evalInitPerson', 'eip')->addSelect('eip')
            ->leftJoin('ep.evalJusticePerson', 'ejp')->addSelect('ejp')
            ->leftJoin('ep.evalAdmPerson', 'eap')->addSelect('eap')
            ->leftJoin('ep.evalBudgetPerson', 'ebp')->addSelect('ebp')
            ->leftJoin('ep.evalFamilyPerson', 'efp')->addSelect('efp')
            ->leftJoin('ep.evalProfPerson', 'epf')->addSelect('epf')
            ->leftJoin('ep.evalSocialPerson', 'esp')->addSelect('esp')

            ->leftJoin('eip.evalBudgetResources', 'eir')->addSelect('eir')
            ->leftJoin('ebp.evalBudgetResources', 'ebr')->addSelect('ebr')
            ->leftJoin('ebp.evalBudgetCharges', 'ebc')->addSelect('ebc')
            ->leftJoin('ebp.evalBudgetDebts', 'ebd')->addSelect('ebd')

            ->leftJoin('ep.evaluationGroup', 'eg')->addSelect('PARTIAL eg.{id}')
            ->leftJoin('eg.evalInitGroup', 'eig')->addSelect('eig')
            ->leftJoin('eg.evalBudgetGroup', 'ebg')->addSelect('ebg')
            ->leftJoin('eg.evalFamilyGroup', 'efg')->addSelect('efg')
            ->leftJoin('eg.evalHousingGroup', 'ehg')->addSelect('ehg')
            ->leftJoin('eg.evalSocialGroup', 'esg')->addSelect('esg')
        ;

        return $this->filters($qb, $search)

            ->setMaxResults($limit)
            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult()
        ;
    }

    /**
     * Compte le nombre de suivis à exporter.
     */
    public function countSupportsToExport($search = null): int
    {
        $qb = $this->getSupportsQuery()
            ->select('count(DISTINCT sp.id)');

        return $this->filters($qb, $search)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * Trouve le dernier suivi social de la personne en fonction de l'utilisateur.
     */
    public function findLastSupport(SupportPerson $supportPerson): ?SupportPerson
    {
        $qb = $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id}')

            ->andWhere('sp.person = :person')
            ->setParameter('person', $supportPerson->getPerson())
            ->andWhere('sp.id != :supportPerson')
            ->setParameter('supportPerson', $supportPerson->getId());

        if (!$this->user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->user->getServices());
        }

        return $qb
            ->orderBy('sp.updatedAt', 'DESC')
            ->setMaxResults(1)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult()
        ;
    }

    /**
     * Donne le Querybuilder d'un suivi.
     */
    protected function getSupportsQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('sp')
        //     $qb->select('DISTINCT sp')
        //         ->groupBy('sp.id');
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople, siSiaoId}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, type, coefficient}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
            ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')
        ;
    }

    protected function getSupportsOfServiceQuery(): QueryBuilder
    {
        return $this->getSupportsQuery()
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')
            ->leftJoin('sg.hotelSupport', 'hs')->addSelect('hs')
        ;
    }

    /**
     * Filtre la recherche.
     */
    protected function filters(QueryBuilder $qb, $search): QueryBuilder
    {
        if (!$this->user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('s.id IN (:services)')
                ->setParameter('services', $this->user->getServices());
        }

        if ($search->getDeleted()) {
            $this->disableFilter($this->_em, 'softdeleteable');
            $qb->andWhere('sp.deletedAt IS NOT NULL');
        }

        if ($search->getHead()) {
            $qb->andWhere('sp.head = :head')
                ->setParameter('head', $search->getHead());
        }

        if ($search->getFullname()) {
            $date = \DateTime::createFromFormat('d/m/Y', $search->getFullname()) ?? false;
            $int = ((int) $search->getFullname());
            if ($date) {
                $qb->andWhere('p.birthdate = :birthdate')
                    ->setParameter('birthdate', $date->format('Y-m-d'));
            } elseif ($int > 0) {
                $qb->andWhere('g.siSiaoId = :id')
                    ->setParameter('id', $int);
            } else {
                $qb->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                    ->setParameter('fullname', '%'.$search->getFullname().'%');
            }
        }

        $qb = $this->addOrganizationFilters($qb, $search);

        if ($search->getStatus()) {
            $qb = $this->addOrWhere($qb, 'sp.status', $search->getStatus());
        }

        $supportDates = $search->getSupportDates();

        if (1 === $supportDates) {
            if ($search->getStart()) {
                $qb->andWhere('sp.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $qb->andWhere('sp.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 === $supportDates) {
            if ($search->getStart()) {
                if ($search->getStart()) {
                    $qb->andWhere('sp.endDate >= :start')
                        ->setParameter('start', $search->getStart());
                }
                if ($search->getEnd()) {
                    $qb->andWhere('sp.endDate <= :end')
                        ->setParameter('end', $search->getEnd());
                }
            }
        }
        if (3 === $supportDates || !$supportDates) {
            if ($search->getStart()) {
                $qb->andWhere('sp.endDate >= :start OR sp.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $qb->andWhere('sp.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search->getFamilyTypologies()) {
            $qb = $this->addOrWhere($qb, 'g.familyTypology', $search->getFamilyTypologies());
        }

        return $qb;
    }

    /**
     * Donne le nombre de suivis des personnes du groupe.
     */
    public function countSupportsOfPeople(PeopleGroup $peopleGroup): int
    {
        $qb = $this->createQueryBuilder('sp')->select('count(sp.id)')
            ->leftJoin('sp.supportGroup', 'sg');

        $qb = $this->addOrWhere($qb, 'sp.person', $peopleGroup->getPeople());

        if (!$this->user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->andWhere('sg.service IN (:services)')
                ->setParameter('services', $this->user->getServices());
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countSupportPeople(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('sp')->select('COUNT(sp.id)')
            ->leftJoin('sp.supportGroup', 'sg');

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
                    $qb->andWhere('sp.status = :status')
                        ->setParameter('status', $value);
                }
                if ('startDate' === $key) {
                    $qb->andWhere('sp.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere('sp.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
            }
        }

        return (int) $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return SupportPerson[]
     */
    public function findPeopleInSupport(SupportGroup $supportGroup): array
    {
        return $this->findPeopleInSupportqueryBuilder($supportGroup)
            ->getQuery()
            ->getResult();
    }

    public function findPeopleInSupportqueryBuilder(SupportGroup $supportGroup): QueryBuilder
    {
        return $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.person', 'p')->addSelect('p')

            ->where('sp.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup);
    }

    public function findPeopleInSupportByIdqueryBuilder(int $supportGroupId): QueryBuilder
    {
        return $this->createQueryBuilder('sp')->select('PARTIAL sp.{id, supportGroup}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, lastname, firstname}')

            ->where('sp.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);
    }

    /**
     * @return SupportPerson[]
     */
    public function findPeopleNotInPlaceGroup(PlaceGroup $placeGroup): array
    {
        $supportPeople = [];

        foreach ($this->findPeopleInSupport($placeGroup->getSupportGroup()) as $supportPerson) {
            if (!$this->personIsInSupport($supportPerson, $placeGroup)) {
                $supportPeople[] = $supportPerson;
            }
        }

        return $supportPeople;
    }

    /**
     * Vérifie si la personne est déjà dans le suivi social.
     */
    protected function personIsInSupport(SupportPerson $supportPerson, PlaceGroup $placeGroup): bool
    {
        foreach ($placeGroup->getPlacePeople() as $placePerson) {
            if ($placePerson->getPerson() === $supportPerson->getPerson()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Donne tous les suivis sociaux de l'utilisateur.
     */
    public function getSupportsOfUserQueryBuilder(User $user, $maxResult = null): QueryBuilder
    {
        return $this->createQueryBuilder('sp')->select('sp, sp.id, p.firstname, p.lastname')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect(('PARTIAL sg.{id, referent, status}'))
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('sg.referent = :referent')
            ->setParameter('referent', $user)
            ->andWhere('sg.status = :status')
            ->setParameter('status', SupportGroup::STATUS_IN_PROGRESS)
            ->andWhere('sp.head = TRUE')

            ->orderBy('p.lastname', 'ASC')
            ->setMaxResults($maxResult);
    }

    /**
     * Donne tous les suivis individuel de l'utilisateur selon le groupe.
     */
    public function getPeopleOfSupportQueryBuilder(User $user, int $supportGroupId): QueryBuilder
    {
        return $this->createQueryBuilder('sp')->select('sp, sp.id, p.firstname, p.lastname')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect(('PARTIAL sg.{id, referent, status}'))
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('sg.referent = :referent')
            ->setParameter('referent', $user)
            ->andWhere('sg.id = :id_supportGroup')
            ->setParameter('id_supportGroup', $supportGroupId)

            ->orderBy('p.lastname', 'ASC');
    }

    public function getPeopleOfSupport(User $user, int $supportGroupId)
    {
        return $this->getPeopleOfSupportQueryBuilder($user, $supportGroupId)
            ->getQuery()
            ->getArrayResult();
    }
}
