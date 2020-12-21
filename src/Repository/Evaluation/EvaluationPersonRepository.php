<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvaluationPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

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
     * Donne toute l'évaluation sociale de la personne.
     */
    public function findEvaluationOfSupportPerson(int $id): ?EvaluationPerson
    {
        return $this->createQueryBuilder('ep')->select('ep')
            ->join('ep.supportPerson', 'sp')->addSelect('PARTIAL sp.{id, person, head, role}')
            ->join('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')

            ->leftJoin('ep.initEvalPerson', 'initEvalPerson')->addSelect('initEvalPerson')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addSelect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'evalBudgetPerson')->addSelect('evalBudgetPerson')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addSelect('evalFamilyPerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addSelect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addSelect('evalSocialPerson')

            ->join('ep.evaluationGroup', 'eg')->addSelect('PARTIAL eg.{id}')
            ->leftJoin('eg.initEvalGroup', 'initEvalGroup')->addSelect('initEvalGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addSelect('evalSocialGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addSelect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addSelect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addSelect('evalHousingGroup')
            ->leftJoin('ep.evalJusticePerson', 'ejp')->addSelect('ejp')
            ->leftJoin('eg.evalHotelLifeGroup', 'ehlg')->addSelect('ehlg')

            ->andWhere('ep.supportPerson = :supportPerson')
            ->setParameter('supportPerson', $id)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }
}
