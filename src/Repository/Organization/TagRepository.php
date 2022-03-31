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

    public function findAllTags(string $category = null)
    {
        return $this->findAllQueryBuilder($category)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }

    public function findAllQueryBuilder(string $category = null): QueryBuilder
    {
        $qb = $this->createQueryBuilder('t');

        if ($category) {
            $qb->where('t.categories IS NULL OR t.categories LIKE :category')
                ->setParameter('category', '%'.$category.'%');
        }

        return $qb->orderBy('t.name', 'ASC');
    }

    public function findTagsQuery(?TagSearch $search = null): Query
    {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.createdBy', 'u1')->addSelect('PARTIAL u1.{id, firstname, lastname}')
            ->leftJoin('t.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}');

        if ($search->getName()) {
            $qb->andWhere('t.name LIKE :name')
                ->setParameter('name', '%'.$search->getName().'%');
        }
        if ($search->getColor()) {
            $qb->andWhere('t.color = :color')
                ->setParameter('color', $search->getColor());
        }
        if ($search->getCategories() && count($search->getCategories()) > 0) {
            foreach ($search->getCategories() as $category) {
                $qb->andWhere('t.categories LIKE :categories')
                    ->setParameter('categories', '%'.$category.'%');
            }
        }

        return $qb
            ->orderBy('t.name', 'ASC')
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    public function findTagByServiceQueryBuilder(Service $service, string $category = null): QueryBuilder
    {
        $qb = $this->findAllQueryBuilder($category)
            ->leftJoin('t.services', 's')

            ->andWhere('s.id = :service')
            ->setParameter('service', $service->getId());

        return $qb;
    }

    /**
     * @return Tag[]
     */
    public function getTagsByService(?Service $service = null, string $category = null): array
    {
        if ($service) {
            $tags = $this->findTagByServiceQueryBuilder($service, $category)
                ->getQuery()
                ->getResult();

            if (count($tags) > 0) {
                return $tags;
            }
        }

        return $this->findAllQueryBuilder($category)
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
}
