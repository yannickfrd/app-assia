<?php

namespace App\Repository;

use App\Entity\Service;
use App\Entity\User;
use App\Form\Model\UserSearch;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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
     * Trouve l'utilisateur par son login ou son adresse email.
     */
    public function findUserByUsernameOrEmail(string $username): ?User
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere('u.username = :username')
            ->setParameter('username', $username)
            ->orWhere('u.email = :email')
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Donne l'utilisateur avec tous ses suivis et rdvs.
     */
    public function findUserById(int $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->select('u')
            ->leftJoin('u.referentSupport', 'sg')->addSelect('PARTIAL sg.{id, status, startDate, endDate, updatedAt}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.groupPeople', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople, createdAt, updatedAt}')
            ->leftJoin('g.rolePeople', 'r')->addSelect('PARTIAL r.{id, role, head}')
            ->leftJoin('r.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')
            ->leftJoin('su.service', 'service')->addSelect('PARTIAL service.{id, name, email, phone1}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')

            ->andWhere('u.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Retourne tous les utilisateurs.
     */
    public function findAllUsersQuery(UserSearch $search): Query
    {
        $query = $this->createQueryBuilder('u')
            ->select('u')
            ->leftJoin('u.createdBy', 'creatorUser')->addSelect('creatorUser')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')
            ->leftJoin('su.service', 's')->addSelect('PARTIAL s.{id,name}')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id,name}');

        if ($search->getFirstname()) {
            $query->andWhere('u.firstname LIKE :firstname')
                ->setParameter('firstname', $search->getFirstname().'%');
        }
        if ($search->getLastname()) {
            $query->andWhere('u.lastname LIKE :lastname')
                ->setParameter('lastname', $search->getLastname().'%');
        }
        if ($search->getPhone()) {
            $query->andWhere('u.phone1 = :phone')
                ->setParameter('phone', $search->getPhone());
        }
        if ($search->getStatus()) {
            $query->andWhere('u.status = :status')
                ->setParameter('status', $search->getStatus());
        }
        if ($search->getPole()) {
            $query->andWhere('p.id = :pole_id')
                ->setParameter('pole_id', $search->getPole());
        }
        if (!$search->getDisabled()) {
            $query->andWhere('u.disabledAt IS NULL');
        }
        if ($search->getServices()->count()) {
            $expr = $query->expr();
            $orX = $expr->orX();
            foreach ($search->getServices() as $service) {
                $orX->add($expr->eq('s.id', $service));
            }
            $query->andWhere($orX);
        }

        $query = $query->orderBy('u.lastActivityAt', 'DESC');

        return $query->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Trouve les utilisateurs pour l'export des données.
     *
     * @return mixed
     */
    public function findUsersToExport(UserSearch $search)
    {
        $query = $this->findAllUsersQuery($search);

        return $query->getResult();
    }

    /**
     * Donne la liste des utilisateurs.
     */
    public function getUsersQueryList(Service $service, User $user = null): QueryBuilder
    {
        return $this->createQueryBuilder('u')->select('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('u.serviceUser', 'r')

            ->where('r.service = :services')
            ->setParameter('services', $service)
            ->orWhere('u.id = :user')
            ->setParameter('user', $user)

            ->orderBy('u.lastname', 'ASC');
    }

    public function findAllUsersFromServices(CurrentUserService $currentUser)
    {
        $query = $this->createQueryBuilder('u')
            ->select('PARTIAL u.{id, firstname, lastname, disabledAt}')
            ->leftJoin('u.userDevices', 'ud')->addSelect('ud')
            ->leftJoin('ud.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->where('u.disabledAt IS NULL');

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->leftJoin('u.serviceUser', 'r')
                ->andWhere('r.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query->orderBy('u.lastname', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la liste des utilisateurs pour les listes déroulantes.
     */
    public function getAllUsersFromServicesQueryList(CurrentUserService $currentUser, int $serviceId = null): QueryBuilder
    {
        $query = $this->createQueryBuilder('u')
            ->select('PARTIAL u.{id, firstname, lastname, disabledAt}')
            ->leftJoin('u.serviceUser', 'su')

            ->where('u.disabledAt IS NULL');

        if ($serviceId) {
            $query = $query->andWhere('su.service = :service')
                    ->setParameter('service', $serviceId);
        }

        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->leftJoin('u.serviceUser', 'r')
                ->andWhere('r.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query->orderBy('u.lastname', 'ASC');
    }

    /**
     * Donne tous les utilisateurs du service.
     *
     * @return mixed
     */
    public function findUsersFromService(Service $service)
    {
        return $this->createQueryBuilder('u')
            ->select('PARTIAL u.{id, firstname, lastname, status, phone1, email, disabledAt}')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')

            ->where('su.service = :service')
            ->setParameter('service', $service)
            ->andWhere('u.disabledAt IS NULL')

            ->orderBy('u.lastname', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * @return mixed
     */
    public function findUsers(array $criteria = null)
    {
        $query = $this->createQueryBuilder('u')
        ->andWhere('u.disabledAt IS NULL');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('status' == $key) {
                    $query = $query->andWhere('u.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        return $query->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function countActiveUsers()
    {
        return $this->createQueryBuilder('u')->select('COUNT(u.id)')
            ->where('u.lastActivityAt >= :delay')
            ->setParameter('delay', new \DateTime('5 minutes ago'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
