<?php

namespace App\Repository\Support;

use App\Entity\Support\HotelSupport;
use App\Service\DoctrineTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method HotelSupport|null find($id, $lockMode = null, $lockVersion = null)
 * @method HotelSupport|null findOneBy(array $criteria, array $orderBy = null)
 * @method HotelSupport[]    findAll()
 * @method HotelSupport[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HotelSupportRepository extends ServiceEntityRepository
{
    use DoctrineTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HotelSupport::class);
    }
}
