<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\AbstractFinance;
use App\Entity\Evaluation\EvalInitResource;
use App\Repository\Evaluation\EvaluationPersonRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:evaluation:remove-duplicate-resource',
    description: 'Remove duplicate resource in evaluations (same type and amount). (temp - to delete)',
)]
class RemoveDuplicateEvalResourceCommand extends Command
{
    use DoctrineTrait;

    private $EvaluationPersonRepo;
    private $em;

    public function __construct(
        EvaluationPersonRepository $EvaluationPersonRepo,
        EntityManagerInterface $em
    ) {
        parent::__construct();

        $this->EvaluationPersonRepo = $EvaluationPersonRepo;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
            ->addOption('flush', 'f', InputOption::VALUE_OPTIONAL, 'Flush in database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');
        $flush = $input->getOption('flush');

        $evaluationPeople = $this->EvaluationPersonRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $count = 0;

        $io->createProgressBar();
        $io->progressStart(count($evaluationPeople) + ($flush ? 1 : 0));

        foreach ($evaluationPeople as $evaluationPerson) {
            /** @var EvalInitResource[] $resources */
            $resources = [];

            $evalInitPerson = $evaluationPerson->getEvalInitPerson();

            if (null === $evalInitPerson) {
                continue;
            }

            foreach ($evalInitPerson->getEvalBudgetResources() as $evalInitResource) {
                if ($this->sameResourceExists($evalInitResource, $resources)) {
                    $this->em->remove($evalInitResource);
                    ++$count;
                }
                $resources[] = $evalInitResource;
            }

            $io->progressAdvance();
        }

        if ($flush) {
            $this->disableListeners($this->em);

            $this->em->flush();

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success("It's fixed! $count duplicate resources removed.");

        return Command::SUCCESS;
    }

    private function sameResourceExists(AbstractFinance $evalBudgetResource, array $resources): bool
    {
        foreach ($resources as $resource) {
            if ($evalBudgetResource->getType() === $resource->getType()
                && $evalBudgetResource->getAmount() === $evalBudgetResource->getAmount()) {
                return true;
            }
        }

        return false;
    }
}
