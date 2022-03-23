<?php

namespace App\Command\Admin;

use App\Repository\Admin\IndicatorRepository;
use App\Service\Indicators\IndicatorsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour créer les indicateurs de la veille.
 */
class CreateDailyIndicatorsCommand extends Command
{
    protected static $defaultName = 'app:indicator:create-last-day';

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

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = (new \DateTime('today'))->modify('-1 day');

        $indicator = $this->indicators->createIndicator($date);

        if ($this->indicatorRepo->findOneBy(['date' => $date])) {
            $io->error('The daily indicators exists already !');

            return Command::FAILURE;
        }

        $this->em->persist($indicator);
        $this->em->flush();

        $io->success('The daily indicators are create !');

        return Command::SUCCESS;
    }
}
