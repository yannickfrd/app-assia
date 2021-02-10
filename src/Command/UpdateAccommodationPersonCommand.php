<?php

namespace App\Command;

use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\Support\AccommodationGroupRepository;

/**
 * Commande pour mettre à jour les prises en charges individuelles.
 */
class UpdateAccommodationPersonCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:accommodationPerson:update:supportPerson';

    protected $repo;
    protected $manager;

    public function __construct(EntityManagerInterface $manager, AccommodationGroupRepository $repo)
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
        $nbAccommodationPeople = 0;
        $countUpdate = 0;

        $accommodationGroups = $this->repo->findAll();

        foreach ($accommodationGroups as $accommodationGroup) {
            $supportGroup = $accommodationGroup->getSupportGroup();

            foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
                ++$nbAccommodationPeople;
                foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                    if (null === $accommodationPerson->getSupportPerson() && $accommodationPerson->getPerson()->getId() === $supportPerson->getPerson()->getId()) {
                        $accommodationPerson->setSupportPerson($supportPerson);
                        ++$countUpdate;
                    }
                }
            }
        }
        $this->manager->flush();

        $message = "[OK] Update AccommodationPerson entities is successfull !\n  ".$countUpdate.' / '.$nbAccommodationPeople;
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }
}
