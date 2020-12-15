<?php

namespace App\Repository\People;

use App\Entity\People\PeopleGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PeopleGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PeopleGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PeopleGroup[]    findAll()
 * @method PeopleGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PeopleGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PeopleGroup::class);
    }

    /**
     * Donne le groupe de personnes.
     */
    public function findPeopleGroupById(int $id): ?PeopleGroup
    {
        return $this->createQueryBuilder('g')->select('g')
            ->leftJoin('g.createdBy', 'createdBy')->addSelect('PARTIAL createdBy.{id, firstname, lastname}')
            ->leftJoin('g.updatedBy', 'updatedBy')->addSelect('PARTIAL updatedBy.{id, firstname, lastname}')
            ->leftJoin('g.rolePeople', 'r')->addSelect('PARTIAL r.{id, role, head}')
            ->leftJoin('r.person', 'p')->addSelect('p')

            ->andWhere('g.id = :id')
            ->setParameter('id', $id)

            ->orderBy('p.birthdate', 'ASC')

            ->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Compte le nombre de groupes de personnes.
     */
    public function countGroups(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('g')->select('COUNT(g.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('startDate' === $key) {
                    $query = $query->andWhere('g.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere('g.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
                if ('createdBy' === $key) {
                    $query = $query->andWhere('g.createdBy = :createdBy')
                        ->setParameter('createdBy', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }
}
