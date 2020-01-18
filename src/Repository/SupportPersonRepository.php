<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\SupportPerson;
use App\Security\CurrentUserService;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method SupportPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportPerson[]    findAll()
 * @method SupportPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportPersonRepository extends ServiceEntityRepository
{
    private $currentUserService;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUserService)
    {
        parent::__construct($registry, SupportPerson::class);

        $this->currentUserService = $currentUserService;
    }

    public function getSupports($supportGroupSearch = null)
    {
        $query =  $this->createQueryBuilder("sp")
            ->select("sp")

            ->leftJoin("sp.person", "p")
            ->addselect("p")

            ->leftJoin("sp.supportGroup", "sg")
            ->addselect("sg")

            ->leftJoin("sg.groupPeople", "g")
            ->addselect("g")

            ->leftJoin("sg.referent", "u")
            ->addselect("u")

            ->leftJoin("sg.service", "s")
            ->addselect("s")

            ->leftJoin("s.pole", "pole")
            ->addselect("pole")

            ->leftJoin("p.rolesPerson", "r")
            ->addselect("r")

            ->leftJoin("r.groupPeople", "groupPeople")
            ->addselect("groupPeople");

        // $query->andWhere("r.groupPeople = g");

        if ($supportGroupSearch) {
            $query = $this->filter($query, $supportGroupSearch);
        }

        return $query->orderBy("sp.startDate", "DESC")
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    protected function filter($query, $supportGroupSearch)
    {
        if (!$this->currentUserService->isRole("ROLE_SUPER_ADMIN")) {

            dd("role");
            // if ($this->currentUserService->isRole("ROLE_ADMIN")) {
            $query->andWhere("s.id IN (:services)")
                ->setParameter("services",  $this->currentUserService->getServices());
            // } else {
            //     $query->andWhere("sp.referent = :user")
            //         ->setParameter("user",  $this->currentUserService->getUser());
            // }
        }
        if ($supportGroupSearch->getFullname()) {
            dd("fullname");
            $query->Where("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter("fullname", '%' . $supportGroupSearch->getFullname() . '%');
        }

        // if ($supportGroupSearch->getBirthdate()) {
        //     $query->andWhere("p.birthdate = :birthdate")
        //         ->setParameter("birthdate", $supportGroupSearch->getBirthdate());
        // }
        // if ($supportGroupSearch->getFamilyTypology()) {
        //     $query->andWhere("g.familyTypology = :familyTypology")
        //         ->setParameter("familyTypology", $supportGroupSearch->getFamilyTypology());
        // }
        // if ($supportGroupSearch->getNbPeople()) {
        //     $query->andWhere("g.nbPeople = :nbPeople")
        //         ->setParameter("nbPeople", $supportGroupSearch->getNbPeople());
        // }
        // if ($supportGroupSearch->getStatus()) {
        //     $query->andWhere("sp.status = :status")
        //         ->setParameter("status", $supportGroupSearch->getStatus());
        // }
        if ($supportGroupSearch->getStatus()) {
            dd("status");

            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getStatus() as $status) {
                $orX->add($expr->eq("sp.status", $status));
            }
            $query->andWhere($orX);
        }

        $supportDates = $supportGroupSearch->getSupportDates();

        if ($supportDates == 1) {
            dd("dates");

            if ($supportGroupSearch->getStartDate()) {
                $query->andWhere("sp.startDate >= :startDate")
                    ->setParameter("startDate", $supportGroupSearch->getStartDate());
            }
            if ($supportGroupSearch->getEndDate()) {
                $query->andWhere("sp.startDate <= :endDate")
                    ->setParameter("endDate", $supportGroupSearch->getEndDate());
            }
        }
        if ($supportDates == 2) {
            if ($supportGroupSearch->getStartDate()) {
                if ($supportGroupSearch->getStartDate()) {
                    $query->andWhere("sp.endDate >= :startDate")
                        ->setParameter("startDate", $supportGroupSearch->getStartDate());
                }
                if ($supportGroupSearch->getEndDate()) {
                    $query->andWhere("sp.endDate <= :endDate")
                        ->setParameter("endDate", $supportGroupSearch->getEndDate());
                }
            }
        }
        if ($supportDates == 3 || !$supportDates) {
            if ($supportGroupSearch->getStartDate()) {
                $query->andWhere("sp.endDate >= :startDate OR sp.endDate IS NULL")
                    ->setParameter("startDate", $supportGroupSearch->getStartDate());
            }
            if ($supportGroupSearch->getEndDate()) {
                $query->andWhere("sp.startDate <= :endDate")
                    ->setParameter("endDate", $supportGroupSearch->getEndDate());
            }
        }

        if ($supportGroupSearch->getReferent()) {
            $query->andWhere("u = :referent")
                ->setParameter("referent", $supportGroupSearch->getReferent());
        }

        if (count($supportGroupSearch->getService())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getService() as $service) {
                $orX->add($expr->eq("sg.service", $service));
            }
            $query->andWhere($orX);
        }

        return $query;
    }


    // /**
    //  * @return SupportPerson[] Returns an array of SupportPerson objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SupportPerson
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
