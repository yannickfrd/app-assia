<?php

namespace App\Command;

use App\Repository\Admin\IndicatorRepository;
use App\Service\Indicators\IndicatorsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour créer les indicateurs de la veille.
 */
class CreateDailyIndicatorsCommand extends Command
{
    protected static $defaultName = 'app:indicator:create-last-day';

    protected $manager;
    protected $repoIndicator;
    protected $indicators;

    public function __construct(EntityManagerInterface $manager, IndicatorRepository $repoIndicator, IndicatorsService $indicators)
    {
        $this->manager = $manager;
        $this->repoIndicator = $repoIndicator;
        $this->indicators = $indicators;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->createDailyIndicators((new \DateTime('today'))->modify('-1 day'));
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Mettre à jour les indicateurs.
     */
    protected function createDailyIndicators(\DateTime $date)
    {
        $indicator = $this->indicators->createIndicator($date);

        if (!$this->repoIndicator->findOneBy(['date' => $date])) {
            $this->manager->persist($indicator);
            $this->manager->flush();

            return '[OK] The daily indicators are create !';
        }

        return '[FAILED] The daily indicators exists already !';
    }
}
