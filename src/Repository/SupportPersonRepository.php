<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\SupportPerson;
use App\Form\Model\ExportSearch;
use App\Security\CurrentUserService;
use App\Form\Model\SupportGroupSearch;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method SupportPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportPerson[]    findAll()
 * @method SupportPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportPersonRepository extends ServiceEntityRepository
{
    private $currentUser;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUser)
    {
        parent::__construct($registry, SupportPerson::class);

        $this->currentUser = $currentUser;
    }

    /**
     * Retourne toutes les places pour l'export.
     */
    public function findSupportsToExport(SupportGroupSearch $search = null)
    {
        $query = $this->getSupportsQuery();

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    protected function getSupportsQuery()
    {
        return $this->createQueryBuilder('sp')
            ->select('sp')
            ->leftJoin('sp.person', 'p')->addselect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')
            ->leftJoin('p.accommodationPeople', 'ap')->addselect('ap')
            ->leftJoin('ap.accommodationGroup', 'ag')->addselect('ag')
            ->leftJoin('ag.accommodation', 'a')->addselect('a')
            ->leftJoin('sp.supportGroup', 'sg')->addselect('sg')
            ->leftJoin('sg.groupPeople', 'g')->addselect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addselect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addselect('PARTIAL s.{id, name}')
            ->leftJoin('s.pole', 'pole')->addselect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.device', 'd')->addselect('PARTIAL d.{id, name}')
            ->leftJoin('sg.originRequest', 'origin')->addselect('origin')
            ->leftJoin('origin.organization', 'orga')->addselect('PARTIAL orga.{id, name}');
    }

    /**
     * Filtre la recherche.
     */
    protected function filter($query, SupportGroupSearch $search)
    {
        if (!$this->currentUser->isRole('ROLE_SUPER_ADMIN')) {
            // if ($this->currentUser->isRole("ROLE_ADMIN")) {
            $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $this->currentUser->getServices());
            // } else {
            //     $query->andWhere("sp.referent = :user")
            //         ->setParameter("user",  $this->currentUser->getUser());
            // }
        }
        if ($search->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter('fullname', '%'.$search->getFullname().'%');
        }

        // if ($search->getBirthdate()) {
        //     $query->andWhere("p.birthdate = :birthdate")
        //         ->setParameter("birthdate", $search->getBirthdate());
        // }
        // if ($search->getFamilyTypology()) {
        //     $query->andWhere("g.familyTypology = :familyTypology")
        //         ->setParameter("familyTypology", $search->getFamilyTypology());
        // }
        // if ($search->getNbPeople()) {
        //     $query->andWhere("g.nbPeople = :nbPeople")
        //         ->setParameter("nbPeople", $search->getNbPeople());
        // }
        // if ($search->getStatus()) {
        //     $query->andWhere("sp.status = :status")
        //         ->setParameter("status", $search->getStatus());
        // }
        if ($search->getStatus()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getStatus() as $status) {
                $orX->add($expr->eq('sp.status', $status));
            }
            $query->andWhere($orX);
        }

        $supportDates = $search->getSupportDates();

        if (1 == $supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sp.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sp.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 == $supportDates) {
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
        if (3 == $supportDates || !$supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sp.endDate >= :start OR sp.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sp.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if (count($search->getReferents())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

        if (count($search->getServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        return $query;
    }

    public function findSupportsFullToExport(ExportSearch $search = null)
    {
        $query = $this->getSupportsQuery();

        $query = $query->leftJoin('sp.evaluationsPerson', 'ep')->addselect('ep')
            ->leftJoin('ep.initEvalPerson', 'initEvalPerson')->addselect('initEvalPerson')
            ->leftJoin('ep.evalJusticePerson', 'evalJusticePerson')->addselect('evalJusticePerson')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addselect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'evalBudgetPerson')->addselect('evalBudgetPerson')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addselect('evalFamilyPerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addselect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addselect('evalSocialPerson')

            ->leftJoin('ep.evaluationGroup', 'eg')->addselect('eg')
            ->leftJoin('eg.initEvalGroup', 'initEvalGroup')->addselect('initEvalGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addselect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addselect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addselect('evalHousingGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addselect('evalSocialGroup');

        $query = $this->filtersExport($query, $search);

        return $query->setMaxResults(5000)
            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function countSupportsToExport($search = null)
    {
        $query = $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.supportGroup', 'sg')->addselect('sg')
            ->select('COUNT(sp.id)');

        $query = $this->filtersExport($query, $search);

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    protected function filtersExport($query, ExportSearch $search)
    {
        if ($search->getStatus()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getStatus() as $status) {
                $orX->add($expr->eq('sp.status', $status));
            }
            $query->andWhere($orX);
        }

        $supportDates = $search->getSupportDates();

        if (1 == $supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sp.startDate >= :start')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sp.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }
        if (2 == $supportDates) {
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
        if (3 == $supportDates || !$supportDates) {
            if ($search->getStart()) {
                $query->andWhere('sp.endDate >= :start OR sp.endDate IS NULL')
                    ->setParameter('start', $search->getStart());
            }
            if ($search->getEnd()) {
                $query->andWhere('sp.startDate <= :end')
                    ->setParameter('end', $search->getEnd());
            }
        }

        if ($search->getReferents() && count($search->getReferents())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

        if ($search->getServices() && count($search->getServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($search->getDevices() && count($search->getDevices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        return $query;
    }
}
