<?php

namespace App\Repository;

use App\Entity\Rdv;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Rdv|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rdv|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rdv[]    findAll()
 * @method Rdv[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RdvRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rdv::class);
    }

    /**
     * Trouve tous les RDV entre 2 dates
     * 
     * @return Rdv[]
     */
    public function findRdvsBetween(\Datetime $start, \Datetime $end)
    {
        return $this->createQueryBuilder("r")
            ->andWhere("r.start >= :start")
            ->setParameter("start", $start)
            ->andWhere("r.start <= :end")
            ->setParameter("end", $end)
            ->orderBy("r.start", "ASC")
            ->getQuery()
            ->getResult();
    }

    /**
     * Donne tous les RDV entre 2 dates par jour
     * 
     * @return Array
     */
    public function FindRdvsBetweenByDay(\Datetime $start, \Datetime $end): array
    {
        $rdvs = $this->findRdvsBetween($start, $end);
        $days = [];

        foreach ($rdvs as $rdv) {
            $date =  $rdv->getStart()->format("Y-m-d");
            if (!isset($days[$date])) {
                $days[] = $date;
            }
            $days[$date][] = $rdv;
        }
        return $days;
    }
}
