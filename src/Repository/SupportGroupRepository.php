<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Query;
use App\Entity\SupportGroup;
use Doctrine\ORM\QueryBuilder;
use App\Security\CurrentUserService;
use App\Form\Model\SupportGroupSearch;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
     * Donne le suivi social avec le groupe et les personnes rattachées.
     */
    public function findFullSupportById(int $id): ?SupportGroup
    {
        $query = $this->getsupportQuery();

        return $query
            ->leftJoin('sg.device', 'd')->addselect('PARTIAL d.{id, name}')
            ->leftJoin('sg.referent', 'ref')->addselect('PARTIAL ref.{id, firstname, lastname}')
            ->leftJoin('sg.referent2', 'ref2')->addselect('PARTIAL ref2.{id, firstname, lastname}')

            ->leftJoin('sg.accommodationGroups', 'ag')->addselect('PARTIAL ag.{id}')
            ->leftJoin('sg.evaluationsGroup', 'eg')->addselect('PARTIAL eg.{id}')
            ->leftJoin('eg.evalHousingGroup', 'ehg')->addselect('PARTIAL ehg.{id, housingStatus, housingAddress, housingCity, housingDept, domiciliation, domiciliationAddress, domiciliationCity, domiciliationDept}')
            ->leftJoin('sg.rdvs', 'rdvs')->addselect('PARTIAL rdvs.{id}')
            ->leftJoin('sg.notes', 'notes')->addselect('PARTIAL notes.{id}')
            ->leftJoin('sg.documents', 'docs')->addselect('PARTIAL docs.{id}')

            ->andWhere('sg.id = :id')
            ->setParameter('id', $id)

            ->orderBy('p.birthdate', 'ASC')

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    protected function getsupportQuery()
    {
        return $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.createdBy', 'user')->addselect('PARTIAL user.{id, firstname, lastname}')
            ->leftJoin('sg.updatedBy', 'user2')->addselect('PARTIAL user2.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addselect('PARTIAL s.{id, name, preAdmission, accommodation, justice}')
            ->leftJoin('sg.supportPeople', 'sp')->addselect('sp')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')
            ->leftJoin('sg.groupPeople', 'g')->addselect('PARTIAL g.{id, familyTypology, nbPeople}')

            ->orderBy('p.birthdate', 'ASC');
    }

    /**
     * Donne tous les suivis sociaux.
     */
    public function findAllSupportsQuery(SupportGroupSearch $search): Query
    {
        $query = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 's')->addselect('PARTIAL s.{id, name}')
            ->leftJoin('sg.device', 'd')->addselect('PARTIAL d.{id, name}')
            ->leftJoin('sg.accommodationGroups', 'ag')->addselect('PARTIAL ag.{id, accommodation}')
            ->leftJoin('ag.accommodation', 'a')->addselect('PARTIAL a.{id, name, address, city}')
            ->leftJoin('sg.supportPeople', 'sp')->addselect('sp')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate}')
            ->leftJoin('sg.groupPeople', 'g')->addselect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addselect('PARTIAL u.{id, firstname, lastname}');

        $query = $this->filter($query, $search);

        return $query->orderBy('sg.startDate', 'DESC')
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
            ->leftJoin('sg.device', 'd')->addselect('PARTIAL d.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id,name}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addselect('p')
            ->leftJoin('sg.groupPeople', 'g')->addselect('g')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id,fullname}')
            ->andWhere('sp.head = TRUE');

        $query = $this->filter($query, $search);

        return $query->orderBy('sg.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Filtres.
     *
     * @return mixed
     */
    protected function filter(QueryBuilder $query, SupportGroupSearch $search)
    {
        if (!$this->currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query->where('s.id IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
        }
        if ($search->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
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
        $query = $this->createQueryBuilder('sg')->select('sg')
            ->leftJoin('sg.service', 'sv')->addselect('PARTIAL sv.{id, name}')
            ->leftJoin('sg.device', 'd')->addselect('PARTIAL d.{id, name}')
            ->leftJoin('sg.groupPeople', 'g')->addselect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.supportPeople', 'sp')->addselect('PARTIAL sp.{id, head, role}')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('sg.referent = :referent')
            ->setParameter('referent', $user)
            ->andWhere('sg.status <= 2')
            ->andWhere('sp.role != 3')

            ->orderBy('p.lastname', 'ASC')

            ->setMaxResults($maxResults)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);

        return new Paginator($query);
    }

    public function countAllSupports(array $criteria = null)
    {
        $query = $this->createQueryBuilder('sg')->select('COUNT(sg.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('user' == $key) {
                    $query = $query->andWhere('sg.referent = :user')
                        ->setParameter('user', $value);
                }
                if ('status' == $key) {
                    $query = $query->andWhere('sg.status = :status')
                        ->setParameter('status', $value);
                }
                if ('service' == $key) {
                    $query = $query->andWhere('sg.service = :service')
                        ->setParameter('service', $value);
                }
                if ('device' == $key) {
                    $query = $query->andWhere('sg.device = :device')
                        ->setParameter('device', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
