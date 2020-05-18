<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\ORM\Query;
use App\Entity\AccommodationPerson;
use App\Security\CurrentUserService;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

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
            ->leftJoin('ap.createdBy', 'user')->addselect('user')
            ->leftJoin('ap.person', 'p')->addselect('p')
            ->leftJoin('ap.accommodationGroup', 'ag')->addselect('ag')
            ->leftJoin('ag.supportGroup', 'sg')->addselect('sg')

            ->andWhere('ap.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    public function findAccommodationPeople(CurrentUserService $currentUser, \DateTime $start, \DateTime $end, Service $service = null)
    {
        $query = $this->createQueryBuilder('ap')->select('ap')
            ->leftJoin('ap.accommodationGroup', 'ag')->addselect('PARTIAL ag.{id, accommodation}')
            ->leftJoin('ag.accommodation', 'a')->addselect('a')
            ->leftJoin('a.service', 's')->addselect('PARTIAL s.{id}')

            ->andWhere('ap.endDate > :start OR ap.endDate IS NULL')->setParameter('start', $start)
            ->andWhere('ap.startDate < :end')->setParameter('end', $end);

        if ($service) {
            $query = $query->andWhere('a.service = :service')
                ->setParameter('service', $service);
        }
        if (!$currentUser->isRole('ROLE_SUPER_ADMIN')) {
            $query = $query->andWhere('s.id IN (:services)')
                ->setParameter('services', $currentUser->getServices());
        }

        return $query
            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
