<?php

namespace App\Repository\Organization;

use App\Entity\Organization\Service;
use App\Entity\Organization\Tag;
use App\Form\Model\Organization\TagSearch;
use App\Repository\Traits\QueryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tag|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tag|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tag[]    findAll()
 * @method Tag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagRepository extends ServiceEntityRepository
{
    use QueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    public function findAllWithPartialLoadGetResult()
    {
        return $this->findAllQueryBuilder()
            ->getQuery()
            // ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function findTagsQuery(?TagSearch $search = null): Query
    {
        $qb = $this->findAllQueryBuilder();

        if ($search->getName()) {
            $qb->andWhere('t.name LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }

        return $qb
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    public function findTagByServiceQueryBuilder(Service $service): QueryBuilder
    {
        return $this->findAllQueryBuilder()
            ->leftJoin('t.services', 's')

            ->andWhere('s.id = :service')
            ->setParameter('service', $service->getId());
    }

    /**
     * @return Tag[]|null
     */
    public function getTagsWithOrWithoutService(?Service $service = null): ?array
    {
        if ($service) {
            $tags = $this->findTagByServiceQueryBuilder($service)
                ->getQuery()
                ->getResult();

            if (count($tags) > 0) {
                return $tags;
            }
        }

        return $this->findAllQueryBuilder()
            ->getQuery()
            ->getResult()
        ;
    }

    public function findTagByService(Service $service): array
    {
        return $this->findTagByServiceQueryBuilder($service)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findAllQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.name', 'ASC');
    }
}
