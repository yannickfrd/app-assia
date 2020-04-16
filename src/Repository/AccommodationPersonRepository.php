<?php

namespace App\Repository;

use App\Entity\AccommodationPerson;
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
    public function findOneById(int $id): ?AccommodationPerson
    {
        return $this->createQueryBuilder('pa')
            ->select('pa')
            ->leftJoin('pa.createdBy', 'user')->addselect('user')
            ->leftJoin('pa.person', 'p')->addselect('p')
            ->leftJoin('pa.accommodationGroup', 'gpa')->addselect('gpa')
            ->leftJoin('gpa.supportGroup', 'sg')->addselect('sg')

            ->andWhere('pa.id = :id')
            ->setParameter('id', $id)

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
