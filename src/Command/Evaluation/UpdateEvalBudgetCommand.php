<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\Charge;
use App\Entity\Evaluation\Debt;
use App\Entity\Evaluation\Resource;
use App\Entity\Evaluation\InitEvalPerson;
use App\Form\Utils\Choices;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Corrige les incohérences dans la situation budgétaire de l'évaluation.
 */
class UpdateEvalBudgetCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:evaluation:evalBudget:update';
    protected static $defaultDescription = 'Fix invalid datas in evaluation budget.';

    protected $evaluationGroupRepo;
    protected $em;
    protected int $count = 0;

    public function __construct(EvaluationGroupRepository $evaluationGroupRepo, EntityManagerInterface $em)
    {
        $this->evaluationGroupRepo = $evaluationGroupRepo;
        $this->em = $em;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('fix', InputArgument::OPTIONAL, 'Fix the problem')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');
        $arg = $input->getArgument('fix');

        $evaluations = $this->evaluationGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbEvaluations = count($evaluations);

        $io->createProgressBar();
        $io->progressStart($nbEvaluations);

        foreach ($evaluations as $evaluationGroup) {
            $resourcesGroupAmt = 0;
            $initResourcesGroupAmt = 0;

            foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
                $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
                if ($evalBudgetPerson) {
                    $this->updateEvalObject($evalBudgetPerson);
                    $resourcesGroupAmt += $evalBudgetPerson->getResourcesAmt();
                }
                $initEvalPerson = $evaluationPerson->getInitEvalPerson();
                if ($initEvalPerson) {
                    $this->updateEvalObject($initEvalPerson);
                    $initResourcesGroupAmt += $initEvalPerson->getResourcesAmt();
                }
            }
            $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();
            if ($evalBudgetGroup) {
                $evalBudgetGroup->setResourcesGroupAmt($resourcesGroupAmt);
                $evaluationGroup->getInitEvalGroup()->setResourcesGroupAmt($initResourcesGroupAmt);
            }

            $io->progressAdvance();
        }

        if ('fix' === $arg) {
            $this->em->flush();
        }

        $io->progressFinish();

        $io->success("The evaluation budget are update !\n ".$this->count.' / '.$nbEvaluations);

        return Command::SUCCESS;
    }

    /**
     * Update evalBudgetPerson or InitEvalPerson.
     *
     * @param EvalBudgetPerson|InitEvalPerson $evalObject
     */
    protected function updateEvalObject(object $evalObject): void
    {
        $variables = [
            'resources' => Resource::RESOURCES,
            'charges' => Charge::CHARGES,
            'debts' => Debt::DEBTS,
        ];

        foreach ($variables as $key => $values) {
            $this->updateVariables($evalObject, $key, $values);
        }
    }

    /**
     * Update resources, charges or debts variables.
     *
     * @param EvalBudgetPerson|InitEvalPerson $evalObject
     */
    protected function updateVariables(object $evalObject, string $name, array $values): void
    {
        if (!method_exists($evalObject, 'get'.$name)) {
            return;
        }

        if (Choices::NO === $evalObject->{'get'.$name}() && $evalObject->{'get'.$name.'Amt'}() > 0) {
            $evalObject->{'set'.$name.'Amt'}(0);
            foreach ($values as $key => $value) {
                $method1 = 'set'.ucfirst($key);
                if (method_exists($evalObject, $method1)) {
                    $evalObject->$method1(0);
                }
                $method2 = 'set'.ucfirst($key).'Amt';
                if (method_exists($evalObject, $method2)) {
                    $evalObject->$method2(0);
                }
                ++$this->count;
            }
        } elseif (Choices::YES === $evalObject->{'get'.$name}() && 0 === $evalObject->{'get'.$name.'Amt'}()) {
            $evalObject->{'set'.$name.'Amt'}(null);
            ++$this->count;
        }
    }
}
