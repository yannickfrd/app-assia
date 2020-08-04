<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\SupportGroupRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour l'adresse des suivis à partir du groupe de places ou de l'évaluaiton sociale (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateLocationSupportsCommand extends Command
{
    protected static $defaultName = 'app:support:update:location';

    protected $repo;
    protected $manager;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateLocationSupports();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Mettre à jour le nb de personnes.
     */
    protected function updateLocationSupports()
    {
        $count = 0;
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {
            if (null == $support->getLocationId()) {
                /** @var AccommodationGroup */
                $accommodationGroup = $support->getAccommodationGroups()[0];

                if ($accommodationGroup && $accommodationGroup->getAccommodation()) {
                    $accommodation = $accommodationGroup->getAccommodation();
                    $support
                    ->setCity($accommodation->getCity())
                    ->setAddress($accommodation->getAddress())
                    ->setZipcode($accommodation->getZipcode())
                    ->setLat($accommodation->getLat())
                    ->setLon($accommodation->getLon())
                    ->setLocationId($accommodation->getLocationId());
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
