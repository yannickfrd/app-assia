<?php

namespace App\Command\Evaluation;

use App\Entity\Support\SupportGroup;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Evaluation\EvaluationCompletionChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CheckEvaluationCompletionCommand extends Command
{
    protected static $defaultName = 'app:evaluation:calculate-completion';
    protected static $defaultDescription = 'Add a short description for your command';

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
            ->addArgument('supportArg', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 100)
            ->addOption('flush', 'f', InputOption::VALUE_OPTIONAL, 'Flush in database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $supportArg = $input->getArgument('supportArg');
        $limit = $input->getOption('limit');
        $flush = $input->getOption('flush');

        $supports = $this->supportGroupRepo->findBy([
            'status' => SupportGroup::STATUS_IN_PROGRESS,
        ], ['updatedAt' => 'DESC'], $limit);

        if ($supportArg) {
            $supports = [$this->supportGroupRepo->find($supportArg)];
        }

        // $io->createProgressBar();
        // $io->progressStart(count($supports) + ($flush ? 1 : 0));

        foreach ($supports as $supportGroup) {
            $evaluationGroup = $supportGroup->getEvaluationsGroup()->first();
            [$score, $ratio] = $this->evaluationCompletionChecker->getScore($evaluationGroup ? $evaluationGroup : null);

            echo PHP_EOL.$ratio.' %';
            // $io->progressAdvance();
        }

        if ($flush) {
            $this->em->flush();
            // $io->progressAdvance();
        }

        // $io->progressFinish();

        $io->success('OK!');

        return Command::SUCCESS;
    }
}
