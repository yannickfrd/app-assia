<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Form\Model\Organization\UserSearch;
use App\Form\Utils\Choices;
use App\Repository\Traits\QueryTrait;
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
    use QueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Trouve l'utilisateur par son login ou son adresse email.
     */
    public function findUser(string $username): ?User
    {
        $query = $this->createQueryBuilder('u')->select('u');

        if (1 === $this->count(['email' => $username])) {
            $query->where('u.email = :email')
            ->setParameter('email', $username);
        } else {
            $query->where('u.username = :username')
            ->setParameter('username', $username);
        }

        return $query->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Donne l'utilisateur avec tous ses suivis et rdvs.
     */
    public function findUserById(int $id): ?User
    {
        return $this->createQueryBuilder('u')->select('u')
            ->leftJoin('u.referentSupport', 'sg')->addSelect('PARTIAL sg.{id, status, startDate, endDate, updatedAt}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople, createdAt, updatedAt}')
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
    public function findUsersQuery(UserSearch $search, ?User $user = null): Query
    {
        $query = $this->queryUsers();

        return $this->filters($query, $search, $user)
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Retourne tous les utilisateurs.
     */
    public function findUsersAdminQuery(UserSearch $search, User $user): Query
    {
        $query = $this->queryUsers();

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $query = $query->leftJoin('u.serviceUser', 'r')
                ->andWhere('r.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        return $this->filters($query, $search, $user)
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    /**
     * Donne le querybuilder des utilisateurs.
     */
    protected function queryUsers(): QueryBuilder
    {
        return $this->createQueryBuilder('u')->select('u')
            // ->leftJoin('u.createdBy', 'creator')->addSelect('PARTIAL creator.{id, lastname, firstname}')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')
            ->leftJoin('su.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id, name}');
    }

    /**
     * Filtre les utilisateurs.
     */
    protected function filters(QueryBuilder $query, UserSearch $search, ?User $user = null): QueryBuilder
    {
        if ($search->getFirstname()) {
            $query->andWhere('u.firstname LIKE :firstname')
                ->setParameter('firstname', '%'.$search->getFirstname().'%');
        }
        if ($search->getLastname()) {
            $query->andWhere('u.lastname LIKE :lastname')
                ->setParameter('lastname', '%'.$search->getLastname().'%');
        }
        if ($search->getPhone()) {
            $query->andWhere('u.phone1 LIKE :phone')
                ->setParameter('phone', '%'.$search->getPhone().'%');
        }

        if (Choices::DISABLED === $search->getDisabled()) {
            $query->andWhere('u.disabledAt IS NOT NULL');
        } elseif (Choices::ACTIVE === $search->getDisabled() || null === $search->getDisabled()) {
            $query->andWhere('u.disabledAt IS NULL');
        }

        if ($search->getStatus()) {
            $query = $this->addOrWhere($query, 'u.status', $search->getStatus());
        }

        $query = $this->addPolesFilter($query, $search);
        $query = $this->addServicesFilter($query, $search);

        if ($user && in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $query = $query->orderBy('u.lastActivityAt', 'DESC');
        } else {
            $query = $query->orderBy('u.lastname', 'ASC');
        }

        return $query;
    }

    /**
     * Trouve les utilisateurs pour l'export des données.
     *
     * @return User[]|null
     */
    public function findUsersToExport(UserSearch $search): ?array
    {
        $query = $this->findUsersQuery($search);

        return $query->getResult();
    }

    /**
     * Donne la liste des utilisateurs.
     */
    public function getUsersQueryBuilder(Service $service = null, User $user = null): QueryBuilder
    {
        return $this->createQueryBuilder('u')->select('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('u.serviceUser', 'r')

            ->where('u.status IN (:status)')
            ->setParameter('status', [1, 2, 3])
            ->andWhere('r.service = :services')
            ->setParameter('services', $service)
            ->orWhere('u.id = :user')
            ->setParameter('user', $user)

            ->orderBy('u.lastname', 'ASC');
    }

    /**
     * Donne la liste des utilisateurs.
     */
    public function getUsersForCalendarQueryBuilder(User $user): QueryBuilder
    {
        $query = $this->createQueryBuilder('u')->select('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('u.serviceUser', 'r');

        return $query->andWhere('r.service IN (:services)')
            ->setParameter('services', $user->getServices())

            ->orWhere('u.id = :user')
            ->setParameter('user', $user)

            ->orderBy('u.lastname', 'ASC');
    }

    /**
     * Donne les utilisateurs des services de l'utilisateur actuel.
     *
     * @return User[]|null
     */
    public function findUsersOfServices(CurrentUserService $currentUser): ?array
    {
        $query = $this->createQueryBuilder('u')->select('PARTIAL u.{id, firstname, lastname, disabledAt}')
            ->leftJoin('u.userDevices', 'ud')->addSelect('ud')
            ->leftJoin('ud.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->where('u.disabledAt IS NULL');

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            if ($currentUser->hasRole('ROLE_ADMIN')) {
                $query = $query->leftJoin('u.serviceUser', 'r')
                    ->andWhere('r.service IN (:services)')
                    ->setParameter('services', $currentUser->getServices());
            } else {
                $query->andWhere('u.id = :user')
                ->setParameter('user', $currentUser->getUser());
            }
        }

        return $query->orderBy('u.lastname', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne les utilisateurs d'un service.
     *
     * @return User[]|null
     */
    public function getUsersOfService(Service $service): ?array
    {
        $query = $this->getReferentsQueryBuilder();

        return $query->andWhere('su.service = :service')
            ->setParameter('service', $service)

            ->orderBy('u.lastname', 'ASC')
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Donne la liste des utilisateurs pour les listes déroulantes.
     */
    public function getReferentsOfServicesQueryBuilder(CurrentUserService $currentUser, Service $service = null): QueryBuilder
    {
        $query = $this->getReferentsQueryBuilder();

        if ($service) {
            $query = $query->andWhere('su.service = :service')
                ->setParameter('service', $service);
        }

        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->leftJoin('u.serviceUser', 'r')
                ->andWhere('r.service IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query->orderBy('u.lastname', 'ASC');
    }

    private function getReferentsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('u')
            ->select('PARTIAL u.{id, firstname, lastname, disabledAt}')
            ->leftJoin('u.serviceUser', 'su')

            ->andWhere('u.disabledAt IS NULL')
            ->andWhere('u.status IN (:status)')
            ->setParameter('status', User::REFERENTS_STATUS);
    }

    /**
     * Donne tous les utilisateurs du service.
     *
     * @return User[]|null
     */
    public function findUsersOfService(Service $service): ?array
    {
        return $this->createQueryBuilder('u')
            ->select('PARTIAL u.{id, firstname, lastname, status, phone1, email, disabledAt}')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')

            ->where('su.service = :service')
            ->setParameter('service', $service)
            ->andWhere('u.disabledAt IS NULL')

            ->orderBy('u.lastname', 'ASC')

            ->getQuery()
            ->getResult();
    }

    /**
     * Donne les utilisateurs selon différents critères.
     *
     * @return User[]|null
     */
    public function findUsers(array $criteria = null): ?array
    {
        $query = $this->createQueryBuilder('u')
        ->andWhere('u.disabledAt IS NULL');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('status' === $key) {
                    $query = $query->andWhere('u.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        return $query->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    /**
     * Compte le nombre d'utilissateurs.
     */
    public function countUsers(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('u')->select('COUNT(u.id)')
            ->where('u.disabledAt IS NULL');

        if ($criteria) {
            $query = $query->leftJoin('u.serviceUser', 'su');

            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $query = $query->andWhere('su.service = :service')
                        ->setParameter('service', $value);
                }
                if ('status' === $key) {
                    $query = $query->andWhere('u.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Compte le nombre d'utilisateurs actifs.
     */
    public function countActiveUsers(): int
    {
        return $this->createQueryBuilder('u')->select('COUNT(u.id)')
            ->where('u.lastActivityAt >= :delay')
            ->setParameter('delay', new \DateTime('5 minutes ago'))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
