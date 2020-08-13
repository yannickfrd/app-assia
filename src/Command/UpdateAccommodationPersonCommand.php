<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Repository\AccommodationGroupRepository;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateAccommodationPersonCommand extends Command
{
    protected static $defaultName = 'app:accommodationPerson:update:supportPerson';

    protected $repo;
    protected $manager;

    public function __construct(EntityManagerInterface $manager, AccommodationGroupRepository $repo)
    {
        $this->repo = $repo;
        $this->manager = $manager;

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

        $this->manager->getFilters()->disable('softdeleteable');
        $accommodationGroups = $this->repo->findAll();

        foreach ($accommodationGroups as $accommodationGroup) {
            $supportGroup = $accommodationGroup->getSupportGroup();

            foreach ($accommodationGroup->getAccommodationPeople() as $accommodationPerson) {
                ++$nbAccommodationPeople;
                foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                    if ($accommodationPerson->getSupportPerson() == null && $accommodationPerson->getPerson()->getId() == $supportPerson->getPerson()->getId()) {
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
