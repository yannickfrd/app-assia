<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\SupportGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvaluationGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationGroup[]    findAll()
 * @method EvaluationGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationGroup::class);
    }

    /**
     * Donne toute l'évaluation sociale du groupe.
     */
    public function findEvaluationOfSupport(int $id): ?EvaluationGroup
    {
        // $lastEvaluationId = $this->getLastEvaluationId($id);
        // if (0 === count($lastEvaluationId)) {
        //     return null;
        // }
        return $this->createQueryBuilder('eg')->select('eg')
            ->join('eg.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, status}')
            ->join('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')

            ->join('sg.service', 's')->addSelect('PARTIAL s.{id, name, email, type, place, preAdmission, justice}')
            ->join('sg.device', 'd')->addSelect('PARTIAL d.{id, name, coefficient, place, contribution, contributionType, contributionRate}')

            ->leftJoin('eg.evaluationPeople', 'ep')->addSelect('ep')
            ->join('ep.supportPerson', 'sp')->addSelect('PARTIAL sp.{id, person, head, role, status}')
            ->join('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate, gender}')

            ->leftJoin('eg.initEvalGroup', 'initEvalGroup')->addSelect('initEvalGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addSelect('evalSocialGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addSelect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addSelect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addSelect('evalHousingGroup')

            ->leftJoin('ep.initEvalPerson', 'initEvalPerson')->addSelect('initEvalPerson')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addSelect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'evalBudgetPerson')->addSelect('evalBudgetPerson')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addSelect('evalFamilyPerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addSelect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addSelect('evalSocialPerson')

            ->leftJoin('ep.evalJusticePerson', 'ejp')->addSelect('ejp')
            ->leftJoin('eg.evalHotelLifeGroup', 'ehlg')->addSelect('ehlg')

            ->andWhere('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $id)
            // ->andWhere('eg.id = :id')
            // ->setParameter('id', $lastEvaluationId)

            ->addOrderBy('sp.status', 'ASC')
            ->addOrderBy('sp.head', 'DESC')
            ->addOrderBy('p.birthdate', 'ASC')

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne les ressources.
     */
    public function findEvaluationResourceById(int $supportGroupId): ?EvaluationGroup
    {
        return $this->createQueryBuilder('eg')->select('eg')
            ->join('eg.supportGroup', 'sg')->addSelect('PARTIAL sg.{id}')
            ->leftJoin('eg.evaluationPeople', 'ep')->addSelect('ep')
            ->leftJoin('eg.evalBudgetGroup', 'ebg')->addSelect('PARTIAL ebg.{id, contributionAmt}')
            ->leftJoin('ep.evalBudgetPerson', 'ebp')->addSelect('PARTIAL ebp.{id, resourcesAmt, salaryAmt}')

            ->andWhere('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroupId)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Donne toute l'évaluation sociale du groupe.
     */
    public function findLastEvaluationOfSupport(SupportGroup $supportGroup): ?EvaluationGroup
    {
        return $this->createQueryBuilder('eg')->select('eg')

            ->andWhere('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)

            ->orderBy('eg.id', 'DESC')
            ->setMaxResults(1)

            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * Compte le nombre d'évaluations.
     */
    public function countEvaluations(array $criteria = null): int
    {
        $query = $this->createQueryBuilder('e')->select('COUNT(e.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('startDate' === $key) {
                    $query = $query->andWhere('e.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $query = $query->andWhere('e.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
            }
        }

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne l'ID de la dernière évaluation sociale du suivi.
     */
    public function getLastEvaluationId(int $id): array
    {
        return $this->createQueryBuilder('eg')->select('eg.id')
            ->where('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $id)
            ->orderBy('eg.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getScalarResult();
    }
}
