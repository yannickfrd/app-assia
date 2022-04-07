<?php

namespace App\Repository\Support;

use App\Entity\Support\Avdl;
use App\Service\DoctrineTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Avdl|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avdl|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avdl[]    findAll()
 * @method Avdl[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvdlRepository extends ServiceEntityRepository
{
    use DoctrineTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avdl::class);
    }

    public function findAvdlOfSupportDeleted(int $supportGroupId)
    {
        $this->disableFilter($this->_em, 'softdeleteable');

        return $this->createQueryBuilder('a')->select('a')

            ->where('a.deletedAt IS NOT null')

            ->andwhere('a.supportGroup = :id')
            ->setParameter('id', $supportGroupId)

            ->getQuery()
            ->getResult();
    }

}
