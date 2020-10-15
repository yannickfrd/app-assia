<?php

namespace App\Command;

use App\Repository\IndicatorRepository;
use App\Service\Indicators\IndicatorsService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le nombre de personnes par suivi (TEMPORAIRE, A SUPPRIMER).
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
        $message = $this->createDailyIndicators((new DateTime('midnight'))->modify('-1 day'));
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Mettre à jour les indicateurs.
     */
    protected function createDailyIndicators(\DateTime $date)
    {
        $indicator = $this->indicators->createIndicator($date);

        if (!$this->repoIndicator->findBy(['date' => $date])) {
            $this->manager->persist($indicator);
            $this->manager->flush();

            return '[OK] The daily indicators are create !';
        }

        return '[FAILED] The daily indicators exists already !';
    }
}
