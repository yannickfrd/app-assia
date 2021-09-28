<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\InitEvalPerson;
use App\Form\Utils\Choices;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Corrige les incorrences dans la situation budgétaire de l'évaluation.
 */
class UpdateEvalBudgetCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:evaluation:evalBudget:update';

    protected $repo;
    protected $manager;
    protected int $count = 0;

    public function __construct(EvaluationGroupRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateEvaluations();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }

    protected function updateEvaluations()
    {
        $evaluations = $this->repo->findBy([], ['updatedAt' => 'DESC'], 1000);

        foreach ($evaluations as $evaluation) {
            $resourcesGroupAmt = 0;
            $initResourcesGroupAmt = 0;
            foreach ($evaluation->getEvaluationPeople() as $evaluationPerson) {
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
            $evalBudgetGroup = $evaluation->getEvalBudgetGroup();
            if ($evalBudgetGroup) {
                $evalBudgetGroup->setResourcesGroupAmt($resourcesGroupAmt);
                $evaluation->getInitEvalGroup()->setResourcesGroupAmt($initResourcesGroupAmt);
            }
        }
        $this->manager->flush();

        return "[OK] The evaluation budget are update ! \n ".$this->count.' / '.count($evaluations);
    }

    /**
     * Update evalBudgetPerson or InitEvalPerson.
     *
     * @param EvalBudgetPerson|InitEvalPerson $evalObject
     */
    protected function updateEvalObject(object $evalObject)
    {
        $variables = [
            'resources' => EvalBudgetPerson::RESOURCES_TYPE,
            'charges' => EvalBudgetPerson::CHARGES_TYPE,
            'debts' => EvalBudgetPerson::DEBTS_TYPE,
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
    protected function updateVariables(object $evalObject, string $name, array $values)
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
