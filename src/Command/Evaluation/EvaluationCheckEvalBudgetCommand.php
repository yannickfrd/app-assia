<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\EvalBudgetCharge;
use App\Entity\Evaluation\EvalBudgetResource;
use App\Entity\Evaluation\EvaluationPerson;
use App\Form\Utils\Choices;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/*
 * Vérifie la création correcte des ressources et des charges. TEMPORAIRE A SUPPRIMER.
 */
class EvaluationCheckEvalBudgetCommand extends Command
{
    protected static $defaultName = 'app:evaluation:check_eval_budget';

    protected $em;

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

        $sumResourcesAmt = 0;
        $sumEvalBudgetResourcesAmt = 0;
        foreach (EvaluationCreateEvalBudgetCommand::RESOURCES as $key => $value) {
            $getMethod = 'get'.ucfirst($value);
            $getAmtMethod = 'get'.ucfirst($value).'Amt';

            foreach ($evaluationPeople as $evaluationPerson) {
                $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();

                if (!$evalBudgetPerson || Choices::YES !== $evalBudgetPerson->getResource()
                    || Choices::YES !== $evalBudgetPerson->$getMethod()) {
                    continue;
                }

                $sumResourcesAmt += $evalBudgetPerson->$getAmtMethod();
            }

            $evalBudgetResources = $this->em->getRepository(EvalBudgetResource::class)->findBy(['type' => $key]);

            /** @var EvalBudgetResource[] $evalBudgetResources */
            foreach ($evalBudgetResources  as $evalBudgetResource) {
                $sumEvalBudgetResourcesAmt += $evalBudgetResource->getAmount();
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->info(number_format($sumResourcesAmt, 0, ',', ' ').' € vs. '.PHP_EOL
            .number_format($sumEvalBudgetResourcesAmt, 0, ',', ' ').' €');

        $io->createProgressBar();
        $io->progressStart(count($evaluationPeople));

        $sumChargesAmt = 0;
        $sumEvalBudgetChargesAmt = 0;
        foreach (EvaluationCreateEvalBudgetCommand::CHARGES as $key => $value) {
            $getMethod = 'get'.ucfirst($value);
            $getAmtMethod = 'get'.ucfirst($value).'Amt';

            foreach ($evaluationPeople as $evaluationPerson) {
                $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();

                if (!$evalBudgetPerson || Choices::YES !== $evalBudgetPerson->getCharge()
                    || Choices::YES !== $evalBudgetPerson->$getMethod()) {
                    continue;
                }

                $sumChargesAmt += $evalBudgetPerson->$getAmtMethod();
            }

            $evalBudgetCharges = $this->em->getRepository(EvalBudgetCharge::class)->findBy(['type' => $key]);

            /** @var EvalBudgetCharge[] $evalBudgetCharges */
            foreach ($evalBudgetCharges  as $evalBudgetCharge) {
                $sumEvalBudgetChargesAmt += $evalBudgetCharge->getAmount();
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->info(number_format($sumChargesAmt, 0, ',', ' ').' € vs. '.PHP_EOL
            .number_format($sumEvalBudgetChargesAmt, 0, ',', ' ').' €');

        $io->success("It's finish !");

        return Command::SUCCESS;
    }
}
