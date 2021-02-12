<?php

namespace App\Command;

use App\Repository\Support\PlaceGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour les prises en charges individuelles.
 */
class UpdatePlacePersonCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:placePerson:update:supportPerson';

    protected $repo;
    protected $manager;

    public function __construct(EntityManagerInterface $manager, PlaceGroupRepository $repo)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Update the supportPerson item in the AccommpdationPerson entities.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $nbPlacePeople = 0;
        $countUpdate = 0;

        $placeGroups = $this->repo->findBy([], ['updatedAt' => 'DESC'], 1000);

        foreach ($placeGroups as $placeGroup) {
            $supportGroup = $placeGroup->getSupportGroup();

            foreach ($placeGroup->getPlacePeople() as $placePerson) {
                ++$nbPlacePeople;
                foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                    if (null === $placePerson->getSupportPerson() && $placePerson->getPerson()->getId() === $supportPerson->getPerson()->getId()) {
                        $placePerson->setSupportPerson($supportPerson);
                        ++$countUpdate;
                    }
                }
            }
        }
        $this->manager->flush();

        $message = "[OK] Update PlacePerson entities is successfull !\n  ".$countUpdate.' / '.$nbPlacePeople;
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }
}
