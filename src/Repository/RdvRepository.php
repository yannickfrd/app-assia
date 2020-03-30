<?php

namespace App\Repository;

use App\Entity\Rdv;
use App\Entity\User;
use Doctrine\ORM\Query;
use App\Entity\SupportGroup;
use App\Form\Model\RdvSearch;
use App\Security\CurrentUserService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Rdv|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rdv|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rdv[]    findAll()
 * @method Rdv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RdvRepository extends ServiceEntityRepository
{
    private $currentUserService;

    public function __construct(ManagerRegistry $registry, CurrentUserService $currentUserService)
    {
        parent::__construct($registry, Rdv::class);

        $this->currentUserService = $currentUserService;
    }

    /**
     * Return all rdvs of group support
     *
     * @param RdvSearch $rdvSearch
     * @return Query
     */
    public function findAllRdvsQuery(RdvSearch $rdvSearch): Query
    {
        $query =  $this->createQueryBuilder("r")->select("r")
            ->leftJoin("r.createdBy", "u")->addselect("PARTIAL u.{id, firstname, lastname}")
            ->leftJoin("r.supportGroup", "sg")->addSelect("sg")
            ->leftJoin("sg.supportPerson", "sp")->addSelect("sp")
            ->leftJoin("sp.person", "p")->addSelect("PARTIAL p.{id, firstname, lastname}");

        if ($rdvSearch->getTitle()) {
            $query->andWhere("r.title LIKE :title")
                ->setParameter("title", '%' . $rdvSearch->getTitle() . '%');
        }

        if ($rdvSearch->getFullname()) {
            $query->andWhere("CONCAT(p.lastname,' ' ,p.firstname) LIKE :fullname")
                ->setParameter("fullname", '%' . $rdvSearch->getFullname() . '%');
        }

        if ($rdvSearch->getStartDate()) {
            $query->andWhere("r.start >= :startDate")
                ->setParameter("startDate", $rdvSearch->getStartDate());
        }
        if ($rdvSearch->getEndDate()) {
            $query->andWhere("r.start <= :endDate")
                ->setParameter("endDate", $rdvSearch->getEndDate());
        }

        if ($rdvSearch->getReferent()) {
            $query->andWhere("CONCAT(u.lastname,' ' ,u.firstname) LIKE :referent")
                ->setParameter("referent", '%' . $rdvSearch->getReferent() . '%');
        }

        if ($rdvSearch->getServices() && count($rdvSearch->getServices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($rdvSearch->getServices() as $service) {
                $orX->add($expr->eq("sg.service", $service));
            }
            $query->andWhere($orX);
        }

        if ($rdvSearch->getDevices() && count($rdvSearch->getDevices())) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($rdvSearch->getDevices() as $device) {
                $orX->add($expr->eq("sg.device", $device));
            }
            $query->andWhere($orX);
        }

        return  $query->orderBy("r.start", "ASC")
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Return all rdvs of group support
     *
     * @param integer $supportGroupId
     * @param RdvSearch $rdvSearch
     * @return Query
     */
    public function findAllRdvsQueryFromSupport(int $supportGroupId, RdvSearch $rdvSearch): Query
    {
        $query =  $this->createQueryBuilder("r")
            ->select("r")
            ->leftJoin("r.createdBy", "u")->addselect("PARTIAL u.{id, firstname, lastname}")
            ->leftJoin("r.supportGroup", "sg")->addSelect("sg")

            ->andWhere("sg.id = :supportGroup")
            ->setParameter("supportGroup", $supportGroupId);

        if ($rdvSearch->getTitle()) {
            $query->andWhere("r.title LIKE :title")
                ->setParameter("title", '%' . $rdvSearch->getTitle() . '%');
        }

        if ($rdvSearch->getStartDate()) {
            $query->andWhere("r.start >= :startDate")
                ->setParameter("startDate", $rdvSearch->getStartDate());
        }
        if ($rdvSearch->getEndDate()) {
            $query->andWhere("r.start <= :endDate")
                ->setParameter("endDate", $rdvSearch->getEndDate());
        }

        return  $query->orderBy("r.createdAt", "DESC")
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Trouve tous les RDV entre 2 dates
     * 
     * @return Rdv[]
     * @param \Datetime $start
     * @param \Datetime $end
     * @param SupportGroup $supportGroup
     * @return mixed
     */
    public function findRdvsBetween(\Datetime $start, \Datetime $end, SupportGroup $supportGroup = null)
    {
        $query = $this->createQueryBuilder("r")->select("r")
            ->leftJoin("r.createdBy", "u")->addselect("u")
            ->leftJoin("r.supportGroup", "s")->addselect("s")

            ->where("r.start >= :start")->setParameter("start", $start)
            ->andWhere("r.start <= :end")->setParameter("end", $end);

        if ($supportGroup) {
            $query->andWhere("r.supportGroup = :supportGroup")->setParameter("supportGroup",  $supportGroup);
        } else {
            $query->andWhere("r.createdBy = :user")
                ->setParameter("user",  $this->currentUserService->getUser());
        }

        return $query->orderBy("r.start", "ASC")
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne tous les RDV entre 2 dates par jour
     *
     * @param \Datetime $start
     * @param \Datetime $end
     * @param SupportGroup $supportGroup
     * @return array
     */
    public function findRdvsBetweenByDay(\Datetime $start, \Datetime $end, SupportGroup $supportGroup = null): array
    {
        $rdvs = $this->findRdvsBetween($start, $end, $supportGroup);
        $days = [];

        foreach ($rdvs as $rdv) {
            $date =  $rdv->getStart()->format("Y-m-d");
            if (!isset($days[$date])) {
                $days[] = $date;
            }
            $days[$date][] = $rdv;
        }
        return $days;
    }

    /**
     * Donne tous les rdvs créés par l'utilisateur
     *
     * @param User $user
     * @param integer $maxResults
     * @return mixed
     */
    public function findAllRdvsFromUser(User $user, int $maxResults = 1000)
    {
        return $this->createQueryBuilder("rdv")
            ->addselect("PARTIAL rdv.{id, title, start, end, location}")

            ->leftJoin("rdv.supportGroup", "sg")->addselect("PARTIAL sg.{id}")
            ->leftJoin("sg.supportPerson", "sp")->addselect("PARTIAL sp.{id, head, role}")
            ->leftJoin("sp.person", "p")->addselect("PARTIAL p.{id, firstname, lastname}")

            ->andWhere("rdv.createdBy = :createdBy")
            ->setParameter("createdBy", $user)
            ->andWhere("sp.head = TRUE")

            ->orderBy("rdv.start", "DESC")

            ->setMaxResults($maxResults)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function countAllRdvs(array $criteria = null)
    {
        $query = $this->createQueryBuilder("rdv")->select("COUNT(rdv.id)");

        if ($criteria) {

            // $query = $query->leftJoin("rdv.supportGroup", "sg")->addselect("PARTIAL sg.{id, referent, status, service, device}");

            foreach ($criteria as $key => $value) {
                if ($key == "user") {
                    $query = $query->andWhere("rdv.createdBy = :user")
                        ->setParameter("user", $value);
                }
                if ($key == "status") {
                    $query = $query->andWhere("sg.status = :status")
                        ->setParameter("status", $value);
                }
                if ($key == "service") {
                    $query = $query->andWhere("sg.service = :service")
                        ->setParameter("service", $value);
                }
                if ($key == "device") {
                    $query = $query->andWhere("sg.device = :device")
                        ->setParameter("device", $value);
                }
            }
        }
        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
