<?php

namespace App\Repository\Support;

use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Support\AccommodationPerson;
use App\Security\CurrentUserService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AccommodationPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method AccommodationPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method AccommodationPerson[]    findAll()
 * @method AccommodationPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccommodationPersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AccommodationPerson::class);
    }

    /**
     * Donne la prise en charge de la personne.
     */
    public function findAccommodationPersonById(int $id): ?AccommodationPerson
    {
        return $this->createQueryBuilder('ap')->select('ap')
            ->leftJoin('ap.createdBy', 'user')->addSelect('user')
            ->leftJoin('ap.person', 'p')->addSelect('p')
            ->leftJoin('ap.accommodationGroup', 'ag')->addSelect('ag')
            ->leftJoin('ag.supportGroup', 'sg')->addSelect('sg')

            ->andWhere('ap.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne les prises en charge des personnes
     *
     * @return AccommodationPerson[]|null
     */
    public function findAccommodationPeople(CurrentUserService $currentUser, \DateTime $start, \DateTime $end, Service $service = null, SubService $subService = null): ?array
    {
        $query = $this->createQueryBuilder('ap')->select('ap')
            ->leftJoin('ap.accommodationGroup', 'ag')->addSelect('PARTIAL ag.{id, accommodation}')
            ->leftJoin('ag.accommodation', 'a')->addSelect('a')
            ->leftJoin('a.service', 's')->addSelect('PARTIAL s.{id}')
            ->leftJoin('a.subService', 'ss')->addSelect('PARTIAL ss.{id}')

            ->andWhere('ap.endDate > :start OR ap.endDate IS NULL')->setParameter('start', $start)
            ->andWhere('ap.startDate < :end')->setParameter('end', $end);

        if ($service) {
            $query = $query->andWhere('a.service = :service')
                ->setParameter('service', $service);
        }
        if ($subService) {
            $query->andWhere('a.subService = :subService')
                ->setParameter('subService', $subService);
        }
        if (!$currentUser->hasRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
