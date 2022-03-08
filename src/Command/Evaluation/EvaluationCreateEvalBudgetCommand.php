<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\EvalBudgetCharge;
use App\Entity\Evaluation\EvalBudgetDebt;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalBudgetResource;
use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Evaluation\EvalInitResource;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\Resource;
use App\Form\Utils\Choices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/*
 * Créé les ressources, les charges et les dettes de toutes les personnes. TEMPORAIRE A SUPPRIMER.
 */
class EvaluationCreateEvalBudgetCommand extends Command
{
    public const RESOURCES = [
        10 => 'salary',
        50 => 'activityBonus',
        30 => 'unemplBenefit',
        60 => 'minimumIncome',
        100 => 'familyAllowance',
        80 => 'disAdultAllowance',
        85 => 'disChildAllowance',
        101 => 'asf',
        90 => 'solidarityAllowance',
        130 => 'asylumAllowance',
        180 => 'scholarships',
        102 => 'familySupplement',
        40 => 'paidTraining',
        120 => 'youthGuarantee',
        170 => 'dailyAllowance',
        200 => 'maintenance',
        210 => 'disabilityPension',
        103 => 'paje',
        20 => 'pensionBenefit',
        1000 => 'ressourceOther',
    ];

    public const CHARGES = [
        50 => 'insurance',
        220 => 'canteen',
        230 => 'consumerCredit',
        40 => 'water',
        20 => 'electricityGas',
        90 => 'childcare',
        70 => 'taxes',
        10 => 'rent',
        60 => 'mutual',
        100 => 'alimony',
        110 => 'phone',
        80 => 'transport',
        1000 => 'chargeOther',
    ];

    public const DEBTS = [
        10 => 'debtRental',
        20 => 'debtConsrCredit',
        30 => 'debtMortgage',
        50 => 'debtFines',
        110 => 'debtHealth',
        60 => 'debtTaxDelays',
        70 => 'debtBankOverdrafts',
        1000 => 'debtOther',
    ];

    protected static $defaultName = 'app:evaluation:create_eval_budget';

    protected $em;

    protected int $nbEvalInitResources = 0;
    protected int $nbEvalBudgetResources = 0;
    protected int $nbEvalBudgetCharges = 0;
    protected int $nbEvalBudgetDebts = 0;
    protected int $nbRepaymentDebts = 0;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $evaluationPeople = $this->em->getRepository(EvaluationPerson::class)->findAll();

        $io->createProgressBar();
        $io->progressStart(count($evaluationPeople));

        foreach ($evaluationPeople as $evaluationPerson) {
            $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();

            $io->progressAdvance();

            $this->createEvalInitResources($evaluationPerson->getEvalInitPerson());

            if (!$evalBudgetPerson) {
                continue;
            }

            $this->createEvalBudgetResources($evalBudgetPerson);
            $this->createEvalBudgetCharges($evalBudgetPerson);
            $this->createEvalBudgetDebts($evalBudgetPerson);
            $this->createRepaymentDebtsCharge($evalBudgetPerson);
        }

        $this->em->flush();

        $io->progressFinish();

        $io->success("It's successful ! 
            - {$this->nbEvalInitResources} init resources created 
            - {$this->nbEvalBudgetResources} resources created 
            - {$this->nbEvalBudgetCharges} charges created 
            - {$this->nbEvalBudgetDebts} debts created 
            - {$this->nbRepaymentDebts} repayment debt charges created"
        );

        return Command::SUCCESS;
    }

    protected function createEvalInitResources(?EvalInitPerson $evalInitPerson = null): void
    {
        if (null === $evalInitPerson || in_array($evalInitPerson->getResource(), [Choices::NO, Choices::NO_INFORMATION, null])) {
            return;
        }

        foreach (self::RESOURCES as $key => $value) {
            $getMethod = 'get'.ucfirst($value);
            $getAmtMethod = 'get'.ucfirst($value).'Amt';

            if (Choices::YES !== $evalInitPerson->$getMethod() && in_array($evalInitPerson->$getAmtMethod(), [0, null])) {
                continue;
            }

            $evalInitResource = (new EvalInitResource())
                ->setEvalInitPerson($evalInitPerson)
                ->setType($key)
                ->setAmount($evalInitPerson->$getAmtMethod())
                ->setComment(Resource::OTHER === $key ? $evalInitPerson->getRessourceOtherPrecision() : null);

            $this->em->persist($evalInitResource);

            ++$this->nbEvalInitResources;
        }
    }

    protected function createEvalBudgetResources(EvalBudgetPerson $evalBudgetPerson): void
    {
        if (in_array($evalBudgetPerson->getResource(), [Choices::NO, Choices::NO_INFORMATION, null])) {
            return;
        }

        foreach (self::RESOURCES as $key => $value) {
            $getMethod = 'get'.ucfirst($value);
            $getAmtMethod = 'get'.ucfirst($value).'Amt';

            if (Choices::YES !== $evalBudgetPerson->$getMethod() && in_array($evalBudgetPerson->$getAmtMethod(), [0, null])) {
                continue;
            }

            $evalBudgetResource = (new EvalBudgetResource())
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($key)
                ->setAmount($evalBudgetPerson->$getAmtMethod())
                ->setComment(Resource::OTHER === $key ? $evalBudgetPerson->getRessourceOtherPrecision() : null);

            $this->em->persist($evalBudgetResource);

            ++$this->nbEvalBudgetResources;
        }
    }

    protected function createEvalBudgetCharges(EvalBudgetPerson $evalBudgetPerson): void
    {
        if (in_array($evalBudgetPerson->getCharge(), [Choices::NO, Choices::NO_INFORMATION, null])) {
            return;
        }

        foreach (self::CHARGES as $key => $value) {
            $getMethod = 'get'.ucfirst($value);
            $getAmtMethod = 'get'.ucfirst($value).'Amt';

            if (Choices::YES !== $evalBudgetPerson->$getMethod() && in_array($evalBudgetPerson->$getAmtMethod(), [0, null])) {
                continue;
            }

            $evalBudgetCharges = (new EvalBudgetCharge())
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($key)
                ->setAmount($evalBudgetPerson->$getAmtMethod())
                ->setComment(EvalBudgetCharge::OTHER === $key ? $evalBudgetPerson->getChargeOtherPrecision() : null);

            $this->em->persist($evalBudgetCharges);

            ++$this->nbEvalBudgetCharges;
        }
    }

    protected function createEvalBudgetDebts(EvalBudgetPerson $evalBudgetPerson): void
    {
        if (in_array($evalBudgetPerson->getDebt(), [Choices::NO, Choices::NO_INFORMATION, null])) {
            return;
        }

        foreach (self::DEBTS as $key => $value) {
            $getMethod = 'get'.ucfirst($value);

            if (Choices::YES !== $evalBudgetPerson->$getMethod()) {
                continue;
            }

            $evalBudgetDebt = (new EvalBudgetDebt())
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($key)
                ->setComment(EvalBudgetDebt::OTHER === $key ? $evalBudgetPerson->getRessourceOtherPrecision() : null);

            $this->em->persist($evalBudgetDebt);

            ++$this->nbEvalBudgetDebts;
        }
    }

    protected function createRepaymentDebtsCharge(EvalBudgetPerson $evalBudgetPerson): void
    {
        if ($evalBudgetPerson->getMonthlyRepaymentAmt() > 0) {
            if (!$this->haveOtherChargeWithDebt($evalBudgetPerson)) {
                $evalBudgetCharge = (new EvalBudgetCharge())
                    ->setEvalBudgetPerson($evalBudgetPerson)
                    ->setType(EvalBudgetCharge::REPAYMENT_DEBT)
                    ->setAmount($evalBudgetPerson->getMonthlyRepaymentAmt());

                $this->em->persist($evalBudgetCharge);

                ++$this->nbRepaymentDebts;
            }
        }
    }

    protected function haveOtherChargeWithDebt(EvalBudgetPerson $evalBudgetPerson): bool
    {
        foreach ($evalBudgetPerson->getEvalBudgetCharges() as $evalBudgetCharge) {
            if (EvalBudgetCharge::OTHER === $evalBudgetCharge->getType() && str_contains(strtolower($evalBudgetCharge->getComment()), 'dette')
                && $evalBudgetPerson->getMonthlyRepaymentAmt() === $evalBudgetCharge->getAmount()) {
                return true;
            }
        }

        return false;
    }
}
