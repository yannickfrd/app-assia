<?php

namespace App\Repository\Organization;

use App\Entity\Organization\ServiceDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ServiceDevice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceDevice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceDevice[]    findAll()
 * @method ServiceDevice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceDeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceDevice::class);
    }
}
