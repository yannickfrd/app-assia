<?php

namespace App\Command\Admin;

use App\Repository\Admin\IndicatorRepository;
use App\Service\Indicators\IndicatorsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:indicator:create-all',
    description: 'Create all daily indicators.',
)]
class CreateIndicatorsCommand extends Command
{
    protected $em;
    protected $indicatorRepo;
    protected $indicators;

    public function __construct(EntityManagerInterface $em, IndicatorRepository $indicatorRepo, IndicatorsService $indicators)
    {
        $this->em = $em;
        $this->indicatorRepo = $indicatorRepo;
        $this->indicators = $indicators;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('start', 's', InputOption::VALUE_OPTIONAL, 'Query limit');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $start = $input->getOption('start');

        $startDate = $start ? new \DateTime($start) : (new \DateTime())->modify('-3 month');

        $diff = $startDate->diff(new \DateTime())->days;

        $date = clone $startDate;
        $count = 0;

        for ($i = 0; $i < $diff; ++$i) {
            $io->text($date->format('Y-m-d'));
            $indicator = $this->indicators->createIndicator($date);
            if (!$this->indicatorRepo->findOneBy(['date' => $date])) {
                $this->em->persist($indicator);
                ++$count;
            }
            $date = $date->modify('+1 day');
        }
        $this->em->flush();

        $io->success("The indicators are create !\n  ".$count);

        return Command::SUCCESS;
    }
}
