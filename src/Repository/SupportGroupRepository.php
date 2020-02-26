<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\Query;
use App\Entity\SupportGroup;
use App\Security\CurrentUserService;
use App\Form\Model\SupportGroupSearch;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
        $query = $this->getsupportQuery();

        return $query->andWhere("sg.id = :id")
            ->setParameter("id", $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne le suivi social avec le groupe et les personnes rattachées
     *
     * @param int $id
     * @return SupportGroup|null
     */
    public function findFullSupportById(int $id): ?SupportGroup
    {
        $query = $this->getsupportQuery();

        return $query
            ->leftJoin("sg.device", "d")->addselect("PARTIAL d.{id, name}")
            ->leftJoin("sg.referent", "ref")->addselect("PARTIAL ref.{id, firstname, lastname}")
            ->leftJoin("sg.referent2", "ref2")->addselect("PARTIAL ref2.{id, firstname, lastname}")

            ->leftJoin("sg.accommodationGroups", "ag")->addselect("PARTIAL ag.{id}")
            ->leftJoin("sg.evaluationsGroup", "eg")->addselect("PARTIAL eg.{id}")
            ->leftJoin("sg.rdvs", "rdvs")->addselect("PARTIAL rdvs.{id}")
            ->leftJoin("sg.notes", "notes")->addselect("PARTIAL notes.{id}")
            ->leftJoin("sg.documents", "docs")->addselect("PARTIAL docs.{id}")

            ->andWhere("sg.id = :id")
            ->setParameter("id", $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    protected function getsupportQuery()
    {
        return $this->createQueryBuilder("sg")
            ->select("sg")
            ->leftJoin("sg.createdBy", "user")->addselect("PARTIAL user.{id, firstname, lastname}")
            ->leftJoin("sg.updatedBy", "user2")->addselect("PARTIAL user2.{id, firstname, lastname}")
            ->leftJoin("sg.service", "s")->addselect("PARTIAL s.{id, name, preAdmission, accommodation, justice}")
            ->leftJoin("sg.supportPerson", "sp")->addselect("sp")
            ->leftJoin("sp.person", "p")->addselect("PARTIAL p.{id, firstname, lastname, birthdate, gender}")
            ->leftJoin("sg.groupPeople", "g")->addselect("PARTIAL g.{id, familyTypology, nbPeople}");
    }

    /**
     * Donne tous les suivis sociaux
     * 
     * @return Query
     */
    public function findAllSupportsQuery(SupportGroupSearch $supportGroupSearch): Query
    {
        $query =  $this->createQueryBuilder("sg")
            ->select("sg")
            ->leftJoin("sg.service", "s")->addselect("PARTIAL s.{id, name}")
            ->leftJoin("sg.device", "d")->addselect("PARTIAL d.{id, name}")
            ->leftJoin("sg.supportPerson", "sp")->addselect("sp")
            ->leftJoin("sp.person", "p")->addselect("PARTIAL p.{id, firstname, lastname, birthdate}")
            ->leftJoin("sg.groupPeople", "g")->addselect("PARTIAL g.{id, familyTypology, nbPeople}")
            ->leftJoin("sg.referent", "u")->addselect("PARTIAL u.{id, firstname, lastname}");

        $query = $this->filter($query, $supportGroupSearch);

        return $query->orderBy("sg.startDate", "DESC")
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    public function getSupports(SupportGroupSearch $supportGroupSearch)
    {
        $query =  $this->createQueryBuilder("sg")
            ->select("sg")
            ->leftJoin("sg.service", "s")->addSelect("PARTIAL s.{id,name}")
            ->leftJoin("sg.device", "d")->addselect("PARTIAL d.{id, name}")
            ->leftJoin("s.pole", "pole")->addSelect("PARTIAL pole.{id,name}")
            ->leftJoin("sg.supportPerson", "sp")->addSelect("sp")
            ->leftJoin("so.person", "p")->addselect("p")
            ->leftJoin("sg.groupPeople", "g")->addselect("g")
            ->leftJoin("sg.referent", "u")->addSelect("PARTIAL u.{id,fullname}")
            ->andWhere("sp.head = TRUE");

        $query = $this->filter($query, $supportGroupSearch);

        return $query->orderBy("sg.startDate", "DESC")
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    protected function filter($query, SupportGroupSearch $supportGroupSearch)
    {
        if (!$this->currentUserService->isRole("ROLE_SUPER_ADMIN")) {
            // if ($this->currentUserService->isRole("ROLE_ADMIN")) {
            $query->where("s.id IN (:services)")
                ->setParameter("services",  $this->currentUserService->getServices());
            // } else {
            //     $query->andWhere("sg.referent = :user")
            //         ->setParameter("user",  $this->currentUserService->getUser());
            // }
        }
        if ($supportGroupSearch->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
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

        if ($supportGroupSearch->getService() && count($supportGroupSearch->getService())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($supportGroupSearch->getService() as $service) {
                $orX->add($expr->eq("sg.service", $service));
            }
            $query->andWhere($orX);
        }

        return $query;
    }

    /**
     * Donne tous les suivis sociaux de l'utilisateur
     *
     */
    public function findAllSupportsFromUser(User $user, $maxResults = null)
    {
        return $this->createQueryBuilder("sg")
            ->select("PARTIAL sg.{id, status, startDate, endDate, updatedAt}")
            ->leftJoin("sg.service", "sv")->addselect("PARTIAL sv.{id, name}")
            ->leftJoin("sg.device", "d")->addselect("PARTIAL d.{id, name}")
            ->leftJoin("sg.groupPeople", "g")->addselect("PARTIAL g.{id, familyTypology, nbPeople}")
            ->leftJoin("sg.supportPerson", "sp")->addselect("PARTIAL sp.{id, head, role}")
            ->leftJoin("sp.person", "person")->addselect("PARTIAL person.{id, firstname, lastname}")

            ->andWhere("sg.referent = :referent")
            ->setParameter("referent", $user)
            ->andWhere("sg.status <= 2")

            ->orderBy("sg.startDate", "DESC")

            ->setMaxResults($maxResults)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
