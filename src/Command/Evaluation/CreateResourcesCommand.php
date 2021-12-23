<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\Charge;
use App\Entity\Evaluation\Debt;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\InitEvalPerson;
use App\Entity\Evaluation\InitResource;
use App\Entity\Evaluation\Resource;
use App\Form\Utils\Choices;
use App\Repository\Evaluation\EvaluationPersonRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/*
 * Créé les ressources, les charges et les dettes de toutes les personnes. TEMPORAIRE A SUPPRIMER.
 */
class CreateResourcesCommand extends Command
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

    protected static $defaultName = 'app:evaluation:create_resources';

    protected $em;
    protected $evaluationPersonRepo;

    protected int $nbInitResources = 0;
    protected int $nbResources = 0;
    protected int $nbCharges = 0;
    protected int $nbDebts = 0;
    protected int $nbRepaymentDebts = 0;

    public function __construct(EntityManagerInterface $em, EvaluationPersonRepository $evaluationPersonRepo)
    {
        $this->em = $em;
        $this->evaluationPersonRepo = $evaluationPersonRepo;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $evaluationPeople = $this->evaluationPersonRepo->findAll();

        $io->createProgressBar();
        $io->progressStart(count($evaluationPeople));

        foreach ($evaluationPeople as $evaluationPerson) {
            $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();

            $io->progressAdvance();

            $this->createInitResources($evaluationPerson->getInitEvalPerson());

            if (!$evalBudgetPerson) {
                continue;
            }

            $this->createResources($evalBudgetPerson);
            $this->createCharges($evalBudgetPerson);
            $this->createDebts($evalBudgetPerson);
            $this->createRepaymentDebtsCharge($evalBudgetPerson);
        }

        $this->em->flush();

        $io->progressFinish();

        $io->success("It's successful ! 
            .\n  - {$this->nbInitResources} init resources created 
            .\n  - {$this->nbResources} resources created 
            .\n  - {$this->nbCharges} charges created 
            .\n  - {$this->nbDebts} debts created 
            .\n  - {$this->nbRepaymentDebts} repayment debt charges created"
        );

        return Command::SUCCESS;
    }

    protected function createInitResources(?InitEvalPerson $initEvalPerson = null): void
    {
        if (null === $initEvalPerson || Choices::YES !== $initEvalPerson->getResource()) {
            return;
        }

        foreach (self::RESOURCES as $key => $value) {
            $getMethod = 'get'.ucfirst($value);

            if (Choices::YES !== $initEvalPerson->$getMethod()) {
                continue;
            }

            $getAmtMethod = 'get'.ucfirst($value).'Amt';

            $initResource = (new InitResource())
                ->setInitEvalPerson($initEvalPerson)
                ->setType($key)
                ->setAmount($initEvalPerson->$getAmtMethod())
                ->setComment(Resource::OTHER === $key ? $initEvalPerson->getRessourceOtherPrecision() : null);

            $this->em->persist($initResource);

            ++$this->nbInitResources;
        }
    }

    protected function createResources(EvalBudgetPerson $evalBudgetPerson): void
    {
        if (Choices::YES !== $evalBudgetPerson->getResource()) {
            return;
        }

        foreach (self::RESOURCES as $key => $value) {
            $getMethod = 'get'.ucfirst($value);

            if (Choices::YES !== $evalBudgetPerson->$getMethod()) {
                continue;
            }

            $getAmtMethod = 'get'.ucfirst($value).'Amt';

            $resource = (new Resource())
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($key)
                ->setAmount($evalBudgetPerson->$getAmtMethod())
                ->setComment(Resource::OTHER === $key ? $evalBudgetPerson->getRessourceOtherPrecision() : null);

            $this->em->persist($resource);

            ++$this->nbResources;
        }
    }

    protected function createCharges(EvalBudgetPerson $evalBudgetPerson): void
    {
        if (Choices::YES !== $evalBudgetPerson->getCharge()) {
            return;
        }

        foreach (self::CHARGES as $key => $value) {
            $getMethod = 'get'.ucfirst($value);

            if (Choices::YES !== $evalBudgetPerson->$getMethod()) {
                continue;
            }

            $getAmtMethod = 'get'.ucfirst($value).'Amt';

            $charge = (new Charge())
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($key)
                ->setAmount($evalBudgetPerson->$getAmtMethod())
                ->setComment(Charge::OTHER === $key ? $evalBudgetPerson->getChargeOtherPrecision() : null);

            $this->em->persist($charge);

            ++$this->nbCharges;
        }
    }

    protected function createDebts(EvalBudgetPerson $evalBudgetPerson): void
    {
        if (Choices::YES != $evalBudgetPerson->getDebt()) {
            return;
        }

        foreach (self::DEBTS as $key => $value) {
            $getMethod = 'get'.ucfirst($value);

            if (Choices::YES !== $evalBudgetPerson->$getMethod()) {
                continue;
            }

            $debt = (new Debt())
                ->setEvalBudgetPerson($evalBudgetPerson)
                ->setType($key)
                ->setComment(Debt::OTHER === $key ? $evalBudgetPerson->getRessourceOtherPrecision() : null);

            $this->em->persist($debt);

            ++$this->nbDebts;
        }
    }

    protected function createRepaymentDebtsCharge(EvalBudgetPerson $evalBudgetPerson): void
    {
        if ($evalBudgetPerson->getMonthlyRepaymentAmt() > 0) {
            if (!$this->haveOtherChargeWithDebt($evalBudgetPerson)) {
                $charge = (new Charge())
                    ->setEvalBudgetPerson($evalBudgetPerson)
                    ->setType(Charge::REPAYMENT_DEBT)
                    ->setAmount($evalBudgetPerson->getMonthlyRepaymentAmt());

                $this->em->persist($charge);

                ++$this->nbRepaymentDebts;
            }
        }
    }

    /**
     * @param Collection<Charge> $charges
     */
    protected function haveOtherChargeWithDebt(EvalBudgetPerson $evalBudgetPerson): bool
    {
        foreach ($evalBudgetPerson->getCharges() as $charge) {
            if (Charge::OTHER === $charge->getType() && str_contains(strtolower($charge->getComment()), 'dette')
                && $evalBudgetPerson->getMonthlyRepaymentAmt() === $charge->getAmount()) {
                return true;
            }
        }

        return false;
    }
}
