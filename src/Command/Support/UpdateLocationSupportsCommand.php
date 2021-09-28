<?php

namespace App\Command\Support;

use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour l'adresse des suivis à partir du groupe de places ou de l'évaluaiton sociale (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateLocationSupportsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:update_location';

    protected $repo;
    protected $manager;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateLocationSupports();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }

    /**
     * Mettre à jour le nb de personnes.
     */
    protected function updateLocationSupports()
    {
        $count = 0;
        $supports = $this->repo->findBy([], ['updatedAt' => 'DESC'], 1000);

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

        return "[OK] The address of supports are update ! \n ".$count.' / '.count($supports);
    }
}
