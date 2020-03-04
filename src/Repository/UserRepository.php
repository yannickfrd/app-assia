<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Service;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Trouve l'utilisateur par son login ou son adresse email
     *
     * @param string $username
     * @return User|null
     */
    public function findUserByUsernameOrEmail($username)
    {
        return $this->createQueryBuilder("u")
            ->select("u")
            ->andWhere("u.username = :username")
            ->setParameter("username", $username)
            ->orWhere("u.email = :email")
            ->setParameter("email", $username)
            ->getQuery()
            ->getOneOrNullResult();
    }


    /**
     * Donne l'utilisateur avec tous ses suivis et rdvs
     *
     * @param int $id
     * @return User|null
     */
    public function findUserById($id): ?User
    {
        return $this->createQueryBuilder("u")
            ->select("u")
            ->leftJoin("u.referentSupport", "sg")->addselect("PARTIAL sg.{id, status, startDate, endDate, updatedAt}")
            ->leftJoin("sg.service", "s")->addselect("PARTIAL s.{id, name, email, phone}")
            ->leftJoin("sg.groupPeople", "g")->addselect("PARTIAL g.{id, familyTypology, nbPeople, createdAt, updatedAt}")
            ->leftJoin("g.rolePerson", "r")->addselect("PARTIAL r.{id, role, head}")
            ->leftJoin("r.person", "p")->addselect("PARTIAL p.{id, firstname, lastname}")
            ->leftJoin("u.serviceUser", "su")->addselect("su")
            ->leftJoin("su.service", "service")->addselect("PARTIAL service.{id, name, email, phone}")
            ->leftJoin("s.pole", "pole")->addselect("PARTIAL pole.{id, name}")

            ->andWhere("u.id = :id")
            ->setParameter("id", $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Retourne toutes les utilisateurs
     * @return Query
     */
    public function findAllUsersQuery($userSearch): Query
    {
        $query =  $this->createQueryBuilder("u")
            ->select("u")
            ->leftJoin("u.serviceUser", "su")->addselect("su")
            ->leftJoin("su.service", "s")->addSelect("PARTIAL s.{id,name}")
            ->leftJoin("s.pole", "p")->addSelect("PARTIAL p.{id,name}");

        if ($userSearch->getFirstname()) {
            $query->andWhere("u.firstname LIKE :firstname")
                ->setParameter("firstname", $userSearch->getFirstname() . '%');
        }
        if ($userSearch->getLastname()) {
            $query->andWhere("u.lastname LIKE :lastname")
                ->setParameter("lastname", $userSearch->getLastname() . '%');
        }
        if ($userSearch->getPhone()) {
            $query->andWhere("u.phone = :phone")
                ->setParameter("phone", $userSearch->getPhone());
        }
        if ($userSearch->getStatus()) {
            $query->andWhere("u.status = :status")
                ->setParameter("status", $userSearch->getStatus());
        }
        if ($userSearch->getPole()) {
            $query->andWhere("p.id = :pole_id")
                ->setParameter("pole_id", $userSearch->getPole());
        }
        if ($userSearch->getenabled()) {
            $query->andWhere("u.enabled = TRUE");
        }

        if ($userSearch->getService()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($userSearch->getService() as $service) {
                $orX->add($expr->eq("s.id", $service));
            }
            $query->andWhere($orX);
        }

        $query = $query->orderBy("u.lastname", "ASC");

        return $query->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    public function findUsersToExport($userSearch)
    {
        $query = $this->findAllUsersQuery($userSearch);
        return $query->getResult();
    }

    /** 
     * Donne la liste des utilisateurs
     */
    public function getUsersQueryList($currentUser, $user = null)
    {
        $query =  $this->createQueryBuilder("u")
            ->select("PARTIAL u.{id, firstname, lastname, enabled}");

        $expr = $query->expr();
        $orX = $expr->orX();

        if (!$currentUser->isRole("ROLE_SUPER_ADMIN")) {
            $query = $query->leftJoin("u.serviceUser", "r")
                ->andWhere("r.service IN (:services)")
                ->setParameter("services", $currentUser->getServices());
            $orX->add($expr->eq("u.id", $currentUser->getUser()));
        }

        // if ($currentUser->isRole("ROLE_ADMIN")) {
        $orX->add($expr->eq("u.enabled", true));
        // }
        if ($user) {
            $orX->add($expr->eq("u.id", $user));
        }
        $query->andWhere($orX);

        return $query->orderBy("u.lastname", "ASC");
    }

    /** 
     * Donne la liste des utilisateurs pour les listes déroulantes
     */
    public function getAllUsersFromServicesQueryList($currentUser)
    {
        $query =  $this->createQueryBuilder("u")
            ->select("PARTIAL u.{id, firstname, lastname, enabled}")
            ->where("u.enabled = TRUE");

        if (!$currentUser->isRole("ROLE_SUPER_ADMIN")) {
            $query = $query->leftJoin("u.serviceUser", "r")
                ->andWhere("r.service IN (:services)")
                ->setParameter("services", $currentUser->getServices());
        }
        return $query->orderBy("u.lastname", "ASC");
    }

    /**
     * Donne tous les utilisateurs du service
     *
     * @return Service|null
     */
    public function findUsersFromService(Service $service)
    {
        return $this->createQueryBuilder("u")
            ->select("PARTIAL u.{id, firstname, lastname, status, phone, email, enabled}")
            ->leftJoin("u.serviceUser", "su")->addselect("su")

            ->where("su.service = :service")
            ->setParameter("service", $service)
            ->andWhere("u.enabled = TRUE")

            ->orderBy("u.lastname", "ASC")

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function countUsers(array $criteria = null)
    {
        $query = $this->createQueryBuilder("u")
            // ->select("u")
            // ->leftJoin("u.referentSupport", "s")->addSelect("PARTIAL s.{id, status, startDate, endDate}")
            // ->join("u.notesCreated", "n")->addSelect("COUNT(n.id)")
            // ->leftJoin("u.rdvs", "r")->addSelect("PARTIAL r.{id, start}")
            // ->leftJoin("u.documents", "d")->addSelect("PARTIAL d.{id}")

            ->andWhere("u.enabled = TRUE");

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ($key == "status") {
                    $query = $query->andWhere("u.status = :status")
                        ->setParameter("status", $value);
                }
            }
        }

        return $query->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
