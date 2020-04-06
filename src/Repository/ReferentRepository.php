<?php

namespace App\Repository;

use App\Entity\Referent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Referent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Referent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Referent[]    findAll()
 * @method Referent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReferentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Referent::class);
    }

    /**
     * Donne tous les groupes de personnes.
     */
    public function findAllReferentsQuery($supportGroupId, $referentSearch): Query
    {
        $query = $this->createQueryBuilder('ref')
            ->andWhere('ref.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId);

        if ($referentSearch->getName()) {
            $query->andWhere('ref.name LIKE :name')
                ->setParameter('name', '%'.$referentSearch->getName().'%');
        }
        if ($referentSearch->getSocialWorker()) {
            $query->andWhere('ref.socialWorker LIKE :socialWorker')
                ->setParameter('socialWorker', '%'.$referentSearch->getSocialWorker().'%');
        }
        if ($referentSearch->getType()) {
            $query->andWhere('ref.type = :type')
                ->setParameter('type', $referentSearch->getType());
        }
        $query = $query->orderBy('ref.createdAt', 'DESC');

        return $query->getQuery();
    }
}
