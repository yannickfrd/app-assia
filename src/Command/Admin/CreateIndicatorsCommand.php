<?php

namespace App\Command\Admin;

use App\Repository\Admin\IndicatorRepository;
use App\Service\Indicators\IndicatorsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour créer les indicateurs généraux.
 */
class CreateIndicatorsCommand extends Command
{
    protected static $defaultName = 'app:indicator:create-all';
    protected static $defaultDescription = 'Create all daily indicators.';

    protected $manager;
    protected $indicatorRepo;
    protected $indicators;

    public function __construct(EntityManagerInterface $manager, IndicatorRepository $indicatorRepo, IndicatorsService $indicators)
    {
        $this->manager = $manager;
        $this->repoIndicator = $indicatorRepo;
        $this->indicators = $indicators;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('start', 's', InputOption::VALUE_OPTIONAL, 'Query limit')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
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
            if (!$this->repoIndicator->findOneBy(['date' => $date])) {
                $this->manager->persist($indicator);
                ++$count;
            }
            $date = $date->modify('+1 day');
        }
        $this->manager->flush();

        $io->success("The indicators are create !\n  ".$count);

        return Command::SUCCESS;
    }
}
