<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\SupportGroup;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SupportGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method SupportGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method SupportGroup[]    findAll()
 * @method SupportGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SupportGroupRepository extends ServiceEntityRepository
{
    private $currentUserService;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUserService)
    {
        parent::__construct($registry, SupportGroup::class);

        $this->currentUserService = $currentUserService;;
    }

    /**
     * Donne le suivi social avec le groupe et les personnes rattachées
     *
     * @param int $id
     * @return SupportGroup|null
     */
    public function findSupportById($id): ?SupportGroup
    {
        return $this->createQueryBuilder("sg")
            ->select("sg")
            ->leftJoin("sg.supportPerson", "sp")
            ->addselect("sp")
            ->leftJoin("sp.person", "p")
            ->addselect("p")
            ->leftJoin("sg.groupPeople", "g")
            ->addselect("g")
            ->leftJoin("g.rolePerson", "r")
            ->addselect("r")
            ->leftJoin("r.person", "p2")
            ->addselect("p2")
            ->andWhere("sg.id = :id")
            ->setParameter("id", $id)
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne tous les suivis sociaux
     * 
     * @return Query
     */
    public function findAllSupports($supportGroupSearch): Query
    {
        $query =  $this->createQueryBuilder("sg")
            ->select("sg")
            ->leftJoin("sg.service", "s")
            ->addselect("s")
            ->leftJoin("sg.supportPerson", "sp")
            ->addselect("sp")
            ->leftJoin("sg.groupPeople", "g")
            ->addselect("g")
            ->leftJoin("sg.referent", "u")
            ->addselect("u")
            ->leftJoin("g.rolePerson", "r")
            ->addselect("r")
            ->leftJoin("r.person", "p")
            ->addselect("p")
            ->andWhere("r.head = TRUE");
        if (!$this->currentUserService->isRole("ROLE_SUPER_ADMIN")) {
            // if ($this->currentUserService->isRole("ROLE_ADMIN")) {
            $query->andWhere("s.id IN (:services)")
                ->setParameter("services",  $this->currentUserService->getServices());
            // } else {
            //     $query->andWhere("sg.referent = :user")
            //         ->setParameter("user",  $this->currentUserService->getUser());
            // }
        }
        if ($supportGroupSearch->getFullname()) {
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
        //     $query->andWhere("sg.status = :status")
        //         ->setParameter("status", $supportGroupSearch->getStatus());
        // }
        if ($supportGroupSearch->getStatus()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getStatus() as $status) {
                $orX->add($expr->eq("sg.status", $status));
            }
            $query->andWhere($orX);
        }

        $supportDates = $supportGroupSearch->getSupportDates();

        if ($supportDates == 1) {
            if ($supportGroupSearch->getStartDate()) {
                $query->andWhere("sg.startDate >= :startDate")
                    ->setParameter("startDate", $supportGroupSearch->getStartDate());
            }
            if ($supportGroupSearch->getEndDate()) {
                $query->andWhere("sg.startDate <= :endDate")
                    ->setParameter("endDate", $supportGroupSearch->getEndDate());
            }
        }
        if ($supportDates == 2) {
            if ($supportGroupSearch->getStartDate()) {
                if ($supportGroupSearch->getStartDate()) {
                    $query->andWhere("sg.endDate >= :startDate")
                        ->setParameter("startDate", $supportGroupSearch->getStartDate());
                }
                if ($supportGroupSearch->getEndDate()) {
                    $query->andWhere("sg.endDate <= :endDate")
                        ->setParameter("endDate", $supportGroupSearch->getEndDate());
                }
            }
        }
        if ($supportDates == 3 || !$supportDates) {
            if ($supportGroupSearch->getStartDate()) {
                $query->andWhere("sg.endDate >= :startDate OR sg.endDate IS NULL")
                    ->setParameter("startDate", $supportGroupSearch->getStartDate());
            }
            if ($supportGroupSearch->getEndDate()) {
                $query->andWhere("sg.startDate <= :endDate")
                    ->setParameter("endDate", $supportGroupSearch->getEndDate());
            }
        }

        if ($supportGroupSearch->getReferent()) {
            $query->andWhere("sg.referent = :referent")
                ->setParameter("referent", $supportGroupSearch->getReferent());
        }

        if ($supportGroupSearch->getService()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getService() as $service) {
                $orX->add($expr->eq("sg.service", $service));
            }
            $query->andWhere($orX);
        }
        return $query->orderBy("sg.startDate", "DESC")
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }
}
