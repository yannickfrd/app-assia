<?php

namespace App\Command\Admin;

use App\Repository\Admin\IndicatorRepository;
use App\Service\Indicators\IndicatorsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour créer les indicateurs généraux.
 */
class CreateIndicatorsCommand extends Command
{
    protected static $defaultName = 'app:indicator:create-all';

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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->createAllIndicators();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }

    /**
     * Mettre à jour les indicateurs.
     */
    protected function createAllIndicators()
    {
        $startDate = (new \DateTime('2020-02-25'));

        $diff = $startDate->diff(new \DateTime())->days;

        $date = clone $startDate;
        $count = 0;

        for ($i = 0; $i < $diff; ++$i) {
            file_put_contents('php://stdout', "\e[36m".$date->format('Y-m-d')."\e[0m \n");
            $indicator = $this->indicators->createIndicator($date);
            if (!$this->repoIndicator->findOneBy(['date' => $date])) {
                $this->manager->persist($indicator);
                ++$count;
            }
            $date = $date->modify('+1 day');
        }
        $this->manager->flush();

        return "[OK] The indicators are create !\n  ".$count;
    }
}
