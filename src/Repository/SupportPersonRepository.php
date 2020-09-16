<?php

namespace App\Repository;

use App\Entity\SupportPerson;
use App\Form\Model\AvdlSupportSearch;
use App\Form\Model\SupportGroupSearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

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
     * Retourne toutes les suivis pour l'export.
     */
    public function findSupportsToExport(?SupportGroupSearch $search = null)
    {
        $query = $this->getSupportsQuery();

        if ($search) {
            $query = $this->filter($query, $search);
        }

        return $query->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Retourne toutes les suivis d'un service pour l'export.
     */
    public function findSupportsFromServiceToExport(?AvdlSupportSearch $search = null, int $serviceId)
    {
        $query = $this->getSupportsFromServiceQuery()

            ->where('sg.service = :service')
            ->setParameter('service', $serviceId);

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
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')
            ->leftJoin('sp.accommodationsPerson', 'ap')->addSelect('ap')
            ->leftJoin('ap.accommodationGroup', 'ag')->addSelect('ag')
            ->leftJoin('ag.accommodation', 'a')->addSelect('a')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.groupPeople', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.subService', 'ss')->addSelect('PARTIAL ss.{id, name}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}')
            ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
            ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}');
    }

    protected function getSupportsFromServiceQuery()
    {
        return $this->createQueryBuilder('sp')
            ->select('sp')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.originRequest', 'origin')->addSelect('origin')
            ->leftJoin('sg.avdl', 'avdl')->addSelect('avdl')
            ->leftJoin('origin.organization', 'orga')->addSelect('PARTIAL orga.{id, name}')
            ->leftJoin('sg.groupPeople', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.referent', 'u')->addSelect('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name}');
    }

    /**
     * Filtre la recherche.
     */
    protected function filter($query, $search)
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

        if ($search->getReferents() && $search->getReferents()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

        if ($search->getServices() && $search->getServices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }
        if ($search->getDevices() && $search->getDevices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getDevices() as $device) {
                $orX->add($expr->eq('sg.device', $device));
            }
            $query->andWhere($orX);
        }

        return $query;
    }

    public function findSupportsFullToExport($search = null)
    {
        $query = $this->getSupportsQuery();

        $query = $query->leftJoin('sp.evaluationsPerson', 'ep')->addSelect('ep')
            ->leftJoin('ep.initEvalPerson', 'initEvalPerson')->addSelect('initEvalPerson')
            ->leftJoin('ep.evalJusticePerson', 'evalJusticePerson')->addSelect('evalJusticePerson')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addSelect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'evalBudgetPerson')->addSelect('evalBudgetPerson')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addSelect('evalFamilyPerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addSelect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addSelect('evalSocialPerson')

            ->leftJoin('ep.evaluationGroup', 'eg')->addSelect('eg')
            ->leftJoin('eg.initEvalGroup', 'initEvalGroup')->addSelect('initEvalGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addSelect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addSelect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addSelect('evalHousingGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addSelect('evalSocialGroup');

        $query = $this->filtersExport($query, $search);

        return $query->setMaxResults(5000)
            ->orderBy('sp.startDate', 'DESC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function countSupportsToExport($search = null)
    {
        $query = $this->createQueryBuilder('sp')->select('sp')
            ->leftJoin('sp.supportGroup', 'sg')->addSelect('sg')
            ->select('COUNT(sp.id)');

        $query = $this->filtersExport($query, $search);

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    protected function filtersExport($query, $search)
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

        if ($search->getReferents() && $search->getReferents()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getReferents() as $referent) {
                $orX->add($expr->eq('sg.referent', $referent));
            }
            $query->andWhere($orX);
        }

        if ($search->getServices() && $search->getServices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('sg.service', $service));
            }
            $query->andWhere($orX);
        }

        if ($search->getDevices() && $search->getDevices()->count()) {
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
