<?php

namespace App\Repository;

use App\Entity\User;
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
     * Retourne toutes les utilisateurs
     * @return Query
     */
    public function findAllUsersQuery($userSearch): Query
    {
        $query =  $this->createQueryBuilder("u")
            ->select("u")
            ->leftJoin("u.serviceUser", "r")
            ->addselect("r")
            ->leftJoin("r.service", "s")
            ->addselect("s")
            ->leftJoin("s.pole", "p")
            ->addselect("p");
        if ($userSearch->getPole()) {
            $query->andWhere("p.id = :pole_id")
                ->setParameter("pole_id", $userSearch->getPole());
        }
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
        if ($userSearch->getService()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($userSearch->getService() as $service) {
                $orX->add($expr->eq("s.id", $service));
            }
            $query->andWhere($orX);
        }
        $query = $query->orderBy("u.lastname", "ASC");
        return $query->getQuery();
    }

    public function findUsersToExport($userSearch)
    {
        $query = $this->findAllUsersQuery($userSearch);
        return $query->getResult();
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

            ->leftJoin("u.referentSupport", "sg")
            ->addselect("sg")
            ->leftJoin("sg.groupPeople", "g")
            ->addselect("g")
            ->leftJoin("g.rolePerson", "r")
            ->addselect("r")
            ->leftJoin("r.person", "p")
            ->addselect("p")

            ->andWhere("u.id = :id")
            ->setParameter("id", $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
