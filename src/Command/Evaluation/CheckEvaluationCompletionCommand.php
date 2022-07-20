<?php

namespace App\Command\Evaluation;

use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use App\Service\Evaluation\EvaluationCompletionChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:evaluation:check-completion',
    description: 'Check the completion of evaluation and return a score.',
)]
class CheckEvaluationCompletionCommand extends Command
{
    use DoctrineTrait;

    private $supportGroupRepo;
    private $evaluationCompletionChecker;
    private $em;

    public function __construct(
        SupportGroupRepository $supportGroupRepo,
        EvaluationCompletionChecker $evaluationCompletionChecker,
        EntityManagerInterface $em
    ) {
        parent::__construct();

        $this->supportGroupRepo = $supportGroupRepo;
        $this->evaluationCompletionChecker = $evaluationCompletionChecker;
        $this->em = $em;
    }

    protected function configure(): void
    {
        $this
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 100)
            ->addOption('flush', 'f', InputOption::VALUE_OPTIONAL, 'Flush in database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');
        $flush = $input->getOption('flush');

        $supports = $this->supportGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);

        $io->createProgressBar();
        $io->progressStart(count($supports) + ($flush ? 1 : 0));

        foreach ($supports as $supportGroup) {
            $evaluationGroup = $supportGroup->getEvaluationsGroup()->first();
            $result = $this->evaluationCompletionChecker->getScore($evaluationGroup ? $evaluationGroup : null);

            $supportGroup->setEvaluationScore($result['score']);

            $io->progressAdvance();
        }

        if ($flush) {
            $this->disableListeners($this->em);

            $this->em->flush();

            $io->progressAdvance();
        }

        $io->progressFinish();

        $io->success("It's successful!");

        return Command::SUCCESS;
    }
}
