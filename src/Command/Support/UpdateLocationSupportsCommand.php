<?php

namespace App\Command\Support;

use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour mettre à jour l'adresse des suivis à partir du groupe de places ou de l'évaluaiton sociale (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateLocationSupportsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:update_location';
    protected static $defaultDescription = 'Update location in supports';

    protected $supportGroupRepo;
    protected $manager;

    public function __construct(SupportGroupRepository $supportGroupRepo, EntityManagerInterface $manager)
    {
        $this->supportGroupRepo = $supportGroupRepo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $count = 0;
        $supports = $this->supportGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);

        foreach ($supports as $support) {
            if (null === $support->getLocationId()) {
                /** @var PlaceGroup */
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
                    /** @var EvaluationGroup */
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
        }

        $this->manager->flush();

        $io->success("The address of supports are update ! \n ".$count.' / '.count($supports));

        return Command::SUCCESS;
    }
}
