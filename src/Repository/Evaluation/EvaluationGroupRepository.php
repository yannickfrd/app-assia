<?php

namespace App\Repository\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\SupportGroup;
use App\Repository\Traits\QueryTrait;
use App\Service\DoctrineTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method EvaluationGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method EvaluationGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method EvaluationGroup[]    findAll()
 * @method EvaluationGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EvaluationGroupRepository extends ServiceEntityRepository
{
    use DoctrineTrait;
    use QueryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationGroup::class);
    }

    /**
     * Donne toute l'√©valuation sociale du groupe.
     */
    public function findEvaluationOfSupport(int $id): ?EvaluationGroup
    {
        // $lastEvaluationId = $this->getLastEvaluationId($id);
        // if (0 === count($lastEvaluationId)) {
        //     return null;
        // }
        return $this->createQueryBuilder('eg')->select('eg')
            ->join('eg.supportGroup', 'sg')->addSelect('sg')
            ->leftJoin('sg.referent', 'r')->addSelect('r')
            ->join('sg.peopleGroup', 'g')->addSelect('PARTIAL g.{id, familyTypology, nbPeople}')
            ->leftJoin('sg.supportPeople', 'sp')->addSelect('sp')
            ->leftJoin('sp.person', 'p')->addSelect('p')

            ->join('sg.service', 's')->addSelect('s')
            ->leftJoin('sg.device', 'd')->addSelect('PARTIAL d.{id, name, code, coefficient, place, contribution, contributionType, contributionRate}')
            ->leftJoin('sg.updatedBy', 'u1')->addSelect('PARTIAL u1.{id, firstname, lastname}')
            ->leftJoin('eg.updatedBy', 'u2')->addSelect('PARTIAL u2.{id, firstname, lastname}')

            ->leftJoin('eg.evaluationPeople', 'ep')->addSelect('ep')

            ->leftJoin('sg.evalInitGroup', 'evalInitGroup')->addSelect('evalInitGroup')
            ->leftJoin('eg.evalSocialGroup', 'evalSocialGroup')->addSelect('evalSocialGroup')
            ->leftJoin('eg.evalBudgetGroup', 'evalBudgetGroup')->addSelect('evalBudgetGroup')
            ->leftJoin('eg.evalFamilyGroup', 'evalFamilyGroup')->addSelect('evalFamilyGroup')
            ->leftJoin('eg.evalHousingGroup', 'evalHousingGroup')->addSelect('evalHousingGroup')

            ->leftJoin('sp.evalInitPerson', 'iep')->addSelect('iep')
            ->leftJoin('ep.evalAdmPerson', 'evalAdmPerson')->addSelect('evalAdmPerson')
            ->leftJoin('ep.evalBudgetPerson', 'ebp')->addSelect('ebp')
            ->leftJoin('ep.evalFamilyPerson', 'evalFamilyPerson')->addSelect('evalFamilyPerson')
            ->leftJoin('ep.evalProfPerson', 'evalProfPerson')->addSelect('evalProfPerson')
            ->leftJoin('ep.evalSocialPerson', 'evalSocialPerson')->addSelect('evalSocialPerson')

            ->leftJoin('iep.evalBudgetResources', 'eir')->addSelect('eir')
            ->leftJoin('ebp.evalBudgetResources', 'ebr')->addSelect('ebr')
            ->leftJoin('ebp.evalBudgetCharges', 'ebc')->addSelect('ebc')
            ->leftJoin('ebp.evalBudgetDebts', 'ebd')->addSelect('ebd')

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
            ->getOneOrNullResult()
        ;
    }

    /**
     * Donne les ressources.
     */
    public function findEvaluationBudget(SupportGroup $supportGroup): ?EvaluationGroup
    {
        return $this->createQueryBuilder('eg')->select('eg')
            ->join('eg.supportGroup', 'sg')->addSelect('PARTIAL sg.{id, startDate, endDate}')
            ->leftJoin('eg.evaluationPeople', 'ep')->addSelect('PARTIAL ep.{id}')
            ->leftJoin('ep.supportPerson', 'sp')->addSelect('PARTIAL sp.{id, startDate, endDate}')
            ->leftJoin('sp.person', 'p')->addSelect('PARTIAL p.{id, firstname, lastname, birthdate}')
            ->leftJoin('eg.evalBudgetGroup', 'ebg')->addSelect('PARTIAL ebg.{id}')
            ->leftJoin('ep.evalBudgetPerson', 'ebp')->addSelect('ebp')
            ->leftJoin('ebp.evalBudgetResources', 'ebr')->addSelect('ebr')
            ->leftJoin('ebp.evalBudgetCharges', 'ebc')->addSelect('ebc')

            ->andWhere('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)

            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Donne toute l'√©valuation sociale du groupe.
     */
    public function findLastEvaluationOfSupport(SupportGroup $supportGroup): ?EvaluationGroup
    {
        return $this->createQueryBuilder('eg')->select('eg')

            ->andWhere('eg.supportGroup = :supportGroup')
            ->setParameter('supportGroup', $supportGroup)

            ->orderBy('eg.id', 'DESC')
            ->setMaxResults(1)

            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Compte le nombre d'√©valuations.
     */
    public function countEvaluations(array $criteria = null): int
    {
        $qb = $this->createQueryBuilder('e')->select('COUNT(e.id)');

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if ('startDate' === $key) {
                    $qb->andWhere('e.createdAt >= :startDate')
                            ->setParameter('startDate', $value);
                }
                if ('endDate' === $key) {
                    $qb->andWhere('e.createdAt <= :endDate')
                            ->setParameter('endDate', $value);
                }
            }
        }

        return $qb
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retourne l'ID de la derni√®re √©valuation sociale du suivi.
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
