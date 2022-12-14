<?php

namespace App\Repository\Organization;

use App\Entity\Event\Alert;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Organization\UserSearch;
use App\Form\Utils\Choices;
use App\Repository\Traits\QueryTrait;
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
        $qb = $this->createQueryBuilder('u')->select('u');

        if (1 === $this->count(['email' => $username])) {
            $qb->where('u.email = :email')
            ->setParameter('email', $username);
        } else {
            $qb->where('u.username = :username')
            ->setParameter('username', $username);
        }

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Donne l'utilisateur avec tous ses suivis et rdvs.
     */
    public function findUserById(int $id): ?User
    {
        return $this->createQueryBuilder('u')->select('u')
            ->leftJoin('u.referentSupports', 'sg')->addSelect('PARTIAL sg.{id, status, startDate, endDate, updatedAt}')
            ->leftJoin('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, phone1}')
            ->leftJoin('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople, createdAt, updatedAt}')
            ->leftJoin('g.rolePeople', 'r')->addSelect('PARTIAL r.{id, role, head}')
            ->leftJoin('r.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')
            ->leftJoin('su.service', 'service')->addSelect('PARTIAL service.{id, name, email, phone1}')
            ->leftJoin('s.pole', 'pole')->addSelect('PARTIAL pole.{id, name}')

            ->andWhere('u.id = :id')
            ->setParameter('id', $id)

            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Retourne tous les utilisateurs.
     */
    public function findUsersQuery(UserSearch $search): Query
    {
        $qb = $this->getQueryUsers();

        $qb = $this->filters($qb, $search);

        return $qb
            ->addOrderBy('u.lastname', 'ASC')
            ->getQuery()
        ;
    }

    /**
     * Retourne tous les utilisateurs.
     */
    public function findUsersAdminQuery(UserSearch $search, User $user): Query
    {
        $qb = $this->getQueryUsers();

        if (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $qb->leftJoin('u.serviceUser', 'r')
                ->andWhere('r.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $qb->orderBy('u.lastActivityAt', 'DESC');
        } else {
            $qb->orderBy('u.lastname', 'ASC');
        }

        return $this->filters($qb, $search)
            ->getQuery()
        ;
    }

    /**
     * Donne le querybuilder des utilisateurs.
     */
    protected function getQueryUsers(): QueryBuilder
    {
        return $this->createQueryBuilder('u')->select('u')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')
            ->leftJoin('su.service', 's')->addSelect('PARTIAL s.{id, name}')
            ->leftJoin('s.pole', 'p')->addSelect('PARTIAL p.{id, name}');
    }

    /**
     * Filtre les utilisateurs.
     */
    protected function filters(QueryBuilder $qb, UserSearch $search): QueryBuilder
    {
        if ($search->getFirstname()) {
            $qb->andWhere('u.firstname LIKE :firstname')
                ->setParameter('firstname', '%'.$search->getFirstname().'%');
        }
        if ($search->getLastname()) {
            $qb->andWhere('u.lastname LIKE :lastname')
                ->setParameter('lastname', '%'.$search->getLastname().'%');
        }
        if ($search->getPhone()) {
            $qb->andWhere('u.phone1 LIKE :phone')
                ->setParameter('phone', '%'.$search->getPhone().'%');
        }

        if (Choices::DISABLED === $search->getDisabled()) {
            $qb->andWhere('u.disabledAt IS NOT NULL');
        } elseif (Choices::ACTIVE === $search->getDisabled() || null === $search->getDisabled()) {
            $qb->andWhere('u.disabledAt IS NULL');
        }

        if ($search->getStatus()) {
            $qb = $this->addOrWhere($qb, 'u.status', $search->getStatus());
        }

        $qb = $this->addPolesFilter($qb, $search, 'p.id');
        $qb = $this->addServicesFilter($qb, $search);

        return $qb;
    }

    /**
     * Trouve les utilisateurs pour l'export des donn??es.
     *
     * @return User[]
     */
    public function findUsersToExport(UserSearch $search): array
    {
        return $this->findUsersQuery($search)
            ->getResult();
    }

    /**
     * Donne la liste des intervanants disponibles dans la page d'??dition du suivi.
     */
    public function getSupportReferentsQueryBuilder(Service $service = null, User $user, User $referent = null): QueryBuilder
    {
        $users = [];
        $users[] = $user->getId();

        if ($referent) {
            $users[] = $referent->getId();
        }

        return $this->getReferentsQueryBuilder()
            ->andWhere('su.service = :service')
            ->setParameter('service', $service)
            ->orWhere('u.id IN (:users)')
            ->setParameter('users', $users);
    }

    /**
     * Donne la liste des utilisateurs.
     */
    public function getUsersForCalendarQueryBuilder(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('u')->select('PARTIAL u.{id, firstname, lastname}')
            ->leftJoin('u.serviceUser', 'r')

            ->andWhere('r.service IN (:services)')
            ->setParameter('services', $user->getServices())
            ->orWhere('u.id = :user')
            ->setParameter('user', $user)

            ->orderBy('u.lastname', 'ASC');
    }

    /**
     * Donne les utilisateurs des services de l'utilisateur actuel.
     *
     * @return User[]
     */
    public function findUsersOfServices(User $user): array
    {
        $qb = $this->createQueryBuilder('u')->select('PARTIAL u.{id, firstname, lastname, disabledAt}')
            ->leftJoin('u.userDevices', 'ud')->addSelect('ud')
            ->leftJoin('ud.device', 'd')->addSelect('PARTIAL d.{id, name}')

            ->where('u.disabledAt IS NULL');

        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            if ($user->hasRole('ROLE_ADMIN')) {
                $qb->leftJoin('u.serviceUser', 'r')
                    ->andWhere('r.service IN (:services)')
                    ->setParameter('services', $user->getServices());
            } else {
                $qb->andWhere('u.id = :user')
                ->setParameter('user', $user);
            }
        }

        return $qb
            ->orderBy('u.lastname', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Donne les utilisateurs d'un service.
     *
     * @return User[]
     */
    public function getUsersOfService(Service $service): array
    {
        return $this->getReferentsQueryBuilder()

            ->andWhere('su.service = :service')
            ->setParameter('service', $service)

            ->getQuery()
            ->getResult();
    }

    /**
     * Donne la liste des utilisateurs pour les listes d??roulantes.
     */
    public function getReferentsOfServicesQueryBuilder(User $user, Service $service = null, string $className = null): QueryBuilder
    {
        $qb = $this->getReferentsQueryBuilder();

        if ($className) {
            $qb = $this->filterByServiceType($qb, $className);
        }
        if ($service) {
            $qb->andWhere('su.service = :service')
                ->setParameter('service', $service);
        }
        if (!$user->hasRole('ROLE_SUPER_ADMIN')) {
            $qb->leftJoin('u.serviceUser', 'r')
                ->andWhere('r.service IN (:services)')
                ->setParameter('services', $user->getServices());
        }

        return $qb;
    }

    private function getReferentsQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('u')->select('u')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')
            ->leftJoin('su.service', 's')->addSelect('s')

            ->andWhere('u.disabledAt IS NULL')
            ->andWhere('u.status IN (:status)')
            ->setParameter('status', User::REFERENTS_STATUS)

            ->orderBy('u.lastname', 'ASC')
        ;
    }

    /**
     * Donne tous les utilisateurs li??s ?? l'utilisateur courant (pour les listes d??roulantes).
     */
    public function findUsersOfCurrentUserQueryBuilder(User $user, ?Service $service = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('u')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')

            ->andWhere('u.disabledAt IS NULL');

        if ($service) {
            $qb->andWhere('su.service = :service')
                ->setParameter('service', $service);
        } elseif (!in_array('ROLE_SUPER_ADMIN', $user->getRoles())) {
            $qb->andWhere('su.service IN (:service)')
                ->setParameter('service', $user->getServices());
        }

        return $qb->orderBy('u.lastname', 'ASC');
    }

    /**
     * Donne tous les utilisateurs du service.
     *
     * @return User[]
     */
    public function findUsersOfService(Service $service): array
    {
        return $this->createQueryBuilder('u')
            ->select('PARTIAL u.{id, firstname, lastname, status, phone1, email, disabledAt}')
            ->leftJoin('u.serviceUser', 'su')->addSelect('su')

            ->where('su.service = :service')
            ->setParameter('service', $service)
            ->andWhere('u.disabledAt IS NULL')

            ->orderBy('u.lastname', 'ASC')

            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Donne les utilisateurs selon diff??rents crit??res.
     *
     * @return User[]
     */
    public function findUsers(array $criteria = null): array
    {
        $qb = $this->createQueryBuilder('u')
        ->andWhere('u.disabledAt IS NULL');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('status' === $key) {
                    $qb->andWhere('u.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre d'utilissateurs.
     */
    public function countUsers(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('u')->select('COUNT(u.id)')
            ->where('u.disabledAt IS NULL');

        if ($criteria) {
            $qb->leftJoin('u.serviceUser', 'su');

            foreach ($criteria as $key => $value) {
                if ('service' === $key) {
                    $qb->andWhere('su.service = :service')
                        ->setParameter('service', $value);
                }
                if ('status' === $key) {
                    $qb->andWhere('u.status = :status')
                        ->setParameter('status', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Donne les utilisateurs avec leur suivis actifs et leur ??valuations sociales.
     *
     * @return User[]
     */
    public function findUsersWithActiveSupportsAndEval(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.setting', 's')->addSelect('s')
            ->leftJoin('u.referentSupports', 'sg')->addSelect('PARTIAL sg.{id, status}')
            ->leftJoin('sg.referent2', 'u2')->addSelect('u2')
            ->leftJoin('sg.evaluationsGroup', 'eg')->addSelect('PARTIAL eg.{id}')
            ->leftJoin('eg.evaluationPeople', 'ep')->addSelect('PARTIAL ep.{id}')

            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('p')
            ->leftJoin('ep.supportPerson', 'sp2')->addSelect('sp2')
            ->leftJoin('sp2.person', 'p2')->addSelect('p2')

            ->leftJoin('ep.evalAdmPerson', 'eap')->addSelect('PARTIAL eap.{id, endValidPermitDate}')
            ->leftJoin('ep.evalSocialPerson', 'esp')->addSelect('PARTIAL esp.{id, endRightsSocialSecurityDate}')
            ->leftJoin('ep.evalBudgetPerson', 'ebp')->addSelect('PARTIAL ebp.{id, endRightsDate}')
            ->leftJoin('ep.evalProfPerson', 'epp')->addSelect('PARTIAL epp.{id, endRqthDate}')
            ->leftJoin('eg.evalHousingGroup', 'ehg')->addSelect('PARTIAL ehg.{id, siaoUpdatedRequestDate, 
                socialHousingUpdatedRequestDate, endDomiciliationDate}')

            ->andWhere('u.disabledAt IS NULL')
            ->andWhere('u.status IN (:user_status)')
            ->setParameter('user_status', User::REFERENTS_STATUS)
            ->andWhere('sg.status = :support_status')
            ->setParameter(':support_status', SupportGroup::STATUS_IN_PROGRESS)

            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Donne les utilisateurs qui ont des rdvs avec des alertes.
     *
     * @return User[]
     */
    public function getUsersWithRdvAlerts(\DateTime $date): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.setting', 's')->addSelect('s')
            ->leftJoin('u.rdvs', 'r')->addSelect('r')
            ->leftJoin('r.alerts', 'a')->addSelect('a')
            ->leftJoin('r.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->andWhere('r.start >= :date1')
            ->setParameter(':date1', (clone $date)->modify('-1 hour'))
            ->andWhere('a.sent != TRUE')
            ->andWhere('a.date <= :date2')
            ->setParameter(':date2', (clone $date)->modify('+5 minutes'))

            ->andWhere('a.type = :type')
            ->setParameter('type', Alert::EMAIL_TYPE)

            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Donne les utilisateurs qui ont des t??ches avec des alertes.
     *
     * @return User[]
     */
    public function getUsersWithTaskAlerts(\DateTime $date): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.setting', 's')->addSelect('s')
            ->leftJoin('u.tasks', 't')->addSelect('t')
            ->leftJoin('t.alerts', 'a')->addSelect('a')
            ->leftJoin('t.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('PARTIAL sp.{id}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname}')

            ->where('a.sent != TRUE')
            ->andWhere('a.date < :date')
            ->setParameter(':date', $date)
            ->andWhere('a.type = :type')
            ->setParameter('type', Alert::EMAIL_TYPE)

            ->getQuery()
            ->getResult()
        ;
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
