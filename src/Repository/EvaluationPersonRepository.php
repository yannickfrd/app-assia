<?php

namespace App\Repository;

use Doctrine\ORM\Query;
use App\Entity\EvaluationPerson;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method EvaluationPerson|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationPerson|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationPerson[]    findAll()
 * @method EvaluationPerson[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationPersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationPerson::class);
    }

    /**
     * Donne toute l'évaluation sociale du groupe
     *
     * @param SupportGroup $supportGroup
     * @return EvaluationPerson|null
     */
    public function findEvaluationsFullToExport($supportGroupSearch = null)
    {
        $query = $this->createQueryBuilder("ep")
            ->select("ep")
            // ->join("ep.supportPerson", "sp")->addselect("PARTIAL sp.{id, person}")
            // ->join("sp.person", "p")->addselect("PARTIAL p.{id, firstname, lastname, birthdate}")

            // ->leftJoin("ep.evaluationGroup", "eg")->addselect("eg")
            // ->join("eg.supportGroup", "sg")->addselect("PARTIAL sg.{id}")
            // ->join("sg.groupPeople", "gp")->addselect("PARTIAL gp.{id, familyTypology, nbPeople}")

            // ->leftJoin("eg.evalSocialGroup", "evalSocialGroup")->addselect("evalSocialGroup")
            // ->leftJoin("eg.evalBudgetGroup", "evalBudgetGroup")->addselect("evalBudgetGroup")
            // ->leftJoin("eg.evalFamilyGroup", "evalFamilyGroup")->addselect("evalFamilyGroup")
            // ->leftJoin("eg.evalHousingGroup", "evalHousingGroup")->addselect("evalHousingGroup")

            ->leftJoin("ep.evalAdmPerson", "evalAdmPerson")->addselect("evalAdmPerson")
            ->leftJoin("ep.evalBudgetPerson", "evalBudgetPerson")->addselect("evalBudgetPerson")
            ->leftJoin("ep.evalFamilyPerson", "evalFamilyPerson")->addselect("evalFamilyPerson")
            ->leftJoin("ep.evalProfPerson", "evalProfPerson")->addselect("evalProfPerson");

        return $query->setMaxResults(502)
            ->orderBy("sp.startDate", "DESC")
            ->getQuery()
            // ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult();
    }
}
