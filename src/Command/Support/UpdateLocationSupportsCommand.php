<?php

namespace App\Command\Support;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Support\PlaceGroup;
use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:support:update_location',
    description: 'Update/ location in supports (temp - to delete).',
)]
class UpdateLocationSupportsCommand extends Command
{
    use DoctrineTrait;

    protected $supportGroupRepo;
    protected $em;

    public function __construct(SupportGroupRepository $supportGroupRepo, EntityManagerInterface $em)
    {
        $this->supportGroupRepo = $supportGroupRepo;
        $this->em = $em;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('fix', InputArgument::OPTIONAL, 'Fix the problem')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $arg = $input->getArgument('fix');

        $supports = $this->supportGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbSupports = count($supports);
        $count = 0;

        $io->createProgressBar();
        $io->progressStart($nbSupports);

        foreach ($supports as $support) {
            if (null === $support->getLocationId()) {
                /** @var PlaceGroup $placeGroup */
                $placeGroup = $support->getPlaceGroups()[0];

                if ($placeGroup && $placeGroup->getPlace()) {
                    $place = $placeGroup->getPlace();
                    $support
                        ->setCity($place->getCity())
                        ->setAddress($place->getAddress())
                        ->setZipcode($place->getZipcode())
                        ->setLat($place->getLat())
                        ->setLon($place->getLon())
                        ->setLocationId($place->getLocationId());
                } else {
                    /** @var EvaluationGroup $evaluation */
                    $evaluation = $support->getEvaluationsGroup()->last();

                    if ($evaluation && $evaluation->getEvalHousingGroup()) {
                        $evalHousingGroup = $evaluation->getEvalHousingGroup();

                        $support
                            ->setCity($evalHousingGroup->getHousingCity())
                            ->setAddress($evalHousingGroup->getHousingAddress())
                            ->setZipcode($evalHousingGroup->getHousingDept());
                    }
                }
                ++$count;
            }

            $io->progressAdvance();
        }

        if ('fix' === $arg) {
            $this->em->flush();
        }

        $io->progressFinish();

        $io->success("The address of supports are update ! \n ".$count.' / '.$nbSupports);

        return Command::SUCCESS;
    }
}
